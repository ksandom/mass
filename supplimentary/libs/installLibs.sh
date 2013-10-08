# Useful install libraries.

settingNames="configDir storageDir installType binExec"

function removeObsoleteStuff
{
	if ! mkdir -p $configDir/obsolete; then
		echo "Mass install: Fatal. Could not create the $configDir/obsolete."
		echo "Check that you can write to $configDir."
		exit 1
	fi
	
	for thing in $things;do
		mv $configDir/$thing* $configDir/obsolete 2>/dev/null
	done
	
	# mv $configDir/obsolete/*available $configDir
	
	if [ `ls $configDir/obsolete 2> /dev/null | wc -l` -lt 1 ]; then
		rmdir $configDir/obsolete
	else
		echo "removeObsoleteStuff: Obsolete stuff has been put in $configDir/obsolete. It is likely that this directory can simply be deleted. But if you have done any custom work, you will want to check that it isn't here first." | tee $configDir/obsolete/readme.md
	fi
	
	rm -f $configDir/config/Verbosity.config.json
}

function checkPrereqs
{
	for prereq in php;do
		if ! which $prereq 1>/dev/null; then
			echo "Could not find $prereq in \$PATH" >&2
			exit 1
		fi
	done
}

function derivePaths
{
	export startDir=`pwd`
	export repoDir="$configDir/repos/$programName"
}

function showConfig
{
	echo "Install config"
	
	for configItem in configDir storageDir installType binExec;do
		oldConfigItem=old$configItem
		if [ "${!oldConfigItem}" == '' ]; then
			echo "	$configItem: 	${!configItem} **NEW**"
		elif [ "${!configItem}" != "${!oldConfigItem}" ]; then
			echo "	$configItem: 	${!configItem} **CHANGED FROM** ${!oldConfigItem}"
		else
			echo "	$configItem: 	${!configItem}"
		fi
	done
	
	echo "	installNotes: 	$installTypeComments"
}

function copyTemplatedFile
{
	src="$1"
	dst="$2"
	
	rm -f "$dst"
	cat $src | sed '
		s#~%configDir%~#'$configDir'#g;
		s#~%storageDir%~#'$storageDir'#g;
		s#~%installType%~#'$installType'#g;
		s#~%binExec%~#'$binExec'#g;
		s#~%programName%~#'$programName'#g;
		s#~%languageName%~#mass#g;
		s#~%.*%~##g' > "$dst"
}

function doInstall
{
	# Migrate any old data changing between a unified directory structure to a split structure.
	mkdir -p "$storageDir"
	if [ "$configDir" != "$storageDir" ]; then
		for dirName in "$configDir"{data,config} ~/.mass/{data,config}; do
			if [ -e "$dirName" ]; then
				lastName=`echo $dirName | sed 's#.*/##g'`
				if [ -e "$storageDir/$lastName" ]; then
					echo "$dirName exists, but $storageDir/$lastName also exists, so no migration will be done."
				else
					echo "$dirName exists, migrating to $storageDir/$lastName."
					mv "$dirName" "$storageDir"
				fi
			fi
		done
	fi

	# Do initial directory structure and test write access
	if mkdir -p "$configDir/"{externalLibraries,repos} "$binExec" "$storageDir/"{data/hosts,config,credentials}
	then
		echo a> $configDir/canWrite
	elif [ "`cat $configDir/canWrite`" != 'a' ]; then
		echo "Could not write to $configDir."
		exit 1
	else
		echo "Mass install: Fatal. Could not create the crucial directories."
		echo "Check that you can write to $configDir."
		exit 1
	fi
	
	rm $configDir/canWrite
	
	# Pre install stuff
	checkPrereqs
	removeObsoleteStuff
	
	showConfig
	
	# Put in the main content
	if [ "$installType" == 'cp' ]; then
		# echo -e "Copying available stuff"
		cp -R "$startDir" "$configDir/repos"
	else
		cd "$configDir/repos"
		ln -sf "$startDir" .
	fi
	
	# Linking like there's no tomorrow.
	cd "$configDir"
	ln -sf "$repoDir"/docs "$repoDir/src/core.php" "$repoDir"/interfaces "$repoDir"/supplimentary .
	rm -f examples
	
	copyTemplatedFile "$startDir/src/index.php" index.php
	
	# Setting up remaining directory structure
	cd "$storageDir"
	mkdir -p config data/1LayerHosts
	
	# Make it executable
	cd "$binExec"
	rm -f "$programName" "manageMass"
	copyTemplatedFile "$startDir/src/exec" "$programName"
	copyTemplatedFile "$startDir/src/manage" manageMass
	chmod 755 "$programName" "manageMass"
	
	# Set up profiles
	createProfile mass
	enableEverythingForProfile mass mass 
	cleanProfile mass

	createProfile massPrivateWebAPI --noExec
	enableEverythingForProfile massPrivateWebAPI mass 
	disableItemInProfile massPrivateWebAPI packages mass-SSH
	cleanProfile massPrivateWebAPI

	cloneProfile massPrivateWebAPI massPublicWebAPI
	disableItemInProfile massPublicWebAPI packages mass-AWS
	cleanProfile massPublicWebAPI
	
	# Cleanup
	rm -f "$configDir/macros-enabled/example"*
	rm -f "$configDir/modules-enabled/example"
	rm -f "$configDir/templates-enabled/example"
	
	if [ ! -f "$configDir/config/Credentials.config.json" ];then
		echo -e "First time setup"
		mass --set=Credentials,defaultKey,id_rsa --saveStoreToConfig=Credentials
	fi
	
	# Run the final stage
	echo -e "Calling the final stage"
	mass --verbosity=2 --finalInstallStage
}

function detectOldSettingsIfWeDontHaveThem
{
	shouldDetect=false
	
	for setting in $settingNames;do
		if [ "${!$setting}" != '' ]; then
			shouldDetect=true
		fi
	done
	
	if [ "$shouldDetect" == 'true' ]; then
		detectOldSettings
	fi
}

function detectOldSettings
{
	if which mass > /dev/null; then
		echo -n "Detecting settings from previous install... "
		
		request=""
		for setting in $settingNames; do
			request="$request~!General,$setting!~	"
		done
		values=`mass --get=Tmp,nonExistent --toString="$request" -s`
		let settingPosition=0
		for setting in $settingNames; do
			let settingPosition=$settingPosition+1
			settingValue=`echo "$values" | cut -d\	  -f $settingPosition`
			if [ "$settingValue" != '' ]; then
				export $setting=$settingValue
				export old$setting=$settingValue
			fi
		done
		
		echo "Done."
	else
		echo "detectOldSettings: No previous install found. Using defaults."
	fi
}

function checkParameters
{
	derivePaths
	
	allowed='^--\(configDir\|storageDir\|binExec\)'
	for parm in $1;do
		parmAction=`echo $parm | cut -d= -f1`
		parmValue=`echo $parm | cut -d= -f2`
		case $parmAction in
			'--help')
				helpFile="docs/installTimeParameters.md"
				if [ -e $helpFile ]; then
					cat "$helpFile"
				else
					echo "Could not find $helpFile. Currently looking from `pwd`."
				fi
				exit 0
			;;
			'--showConfig')
				showConfig
				exit 0
			;;
			'--dontDetect')
				echo "checkParameters: User requested not to detect previous settings."
			;;
			'--defaults')
				echo "checkParameters: User requested to migrate from the previous settings to the defaults."
			;;
			*)
				if [ "`echo $parmAction| grep "$allowed"`" != "" ]; then
					if [ "$parmValue" != "$parmAction" ]; then
						varName=`echo $parmAction| cut -b 3-`
						echo "Will set $varName to $parmValue."
						export "$varName=$parmValue"
					else
						echo "A value must be specified for $parmAction in the form $parmAction=value."
						exit 0
					fi
				else
					echo "Unknown parameter $parm."
					exit 1
				fi
			;;
		esac
	done
}
