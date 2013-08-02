# Useful install libraries.

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
	echo "Install config
	what: 		$programName
	config: 	$configDir
	storage: 	$storageDir
	binExec: 	$binExec
	installType: 	$installType
	installNotes: 	$installTypeComments"
	
	#startDir: 	$startDir
	# 	repoDir: $repoDir
}

function doInstall
{
	if mkdir -p "$configDir/"{data/hosts,externalLibraries,credentials} "$binExec" "$configDir/repos"
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
	
	checkPrereqs
	removeObsoleteStuff
	
	showConfig
	
	if [ "$installType" == 'cp' ]; then
		# echo -e "Copying available stuff"
		cp -R "$startDir" "$configDir/repos"
	else
		cd "$configDir/repos"
		ln -sf "$startDir" .
	fi
	
	cd "$configDir"
	# echo -e "Linking like there's no tomorrow."
	ln -sf "$repoDir"/docs "$repoDir/core.php" "$repoDir"/interfaces "$repoDir"/supplimentary .
	rm -f examples
	
	# "$repoDir"/modules-*available "$repoDir"/macros-*available "$repoDir"/templates-*available "$repoDir"/packages-*available
	
	ln -sf "$repoDir/index.php" .
	
	# echo -e "Setting up remaining directory structure"
	mkdir -p config data/1LayerHosts
	
	cd $binExec
	rm -f "$programName" "manageMass"
	cat "$startDir/$programName" | sed 's#~%configDir%~#'$configDir'#g;s#~%storageDir%~#'$storageDir'#g' > "$programName"
	cp "$startDir/manageMass" .
	chmod 755 "$programName" "manageMass"
	
	createProfile commandLine
	enableEverythingForProfile commandLine mass 
	cleanProfile commandLine

	createProfile privateWebAPI
	enableEverythingForProfile privateWebAPI mass 
	disableItemInProfile privateWebAPI packages mass-SSH
	cleanProfile privateWebAPI

	cloneProfile privateWebAPI publicWebAPI
	disableItemInProfile publicWebAPI packages mass-AWS
	cleanProfile publicWebAPI
	
	# echo -e "Cleanup"
	rm -f "$configDir/macros-enabled/example"*
	rm -f "$configDir/modules-enabled/example"
	rm -f "$configDir/templates-enabled/example"
	
	if [ ! -f "$configDir/config/Credentials.config.json" ];then
		echo -e "First time setup"
		mass --set=Credentials,defaultKey,id_rsa --saveStoreToConfig=Credentials
	fi
	
	# It should be safe to do this on an existing setup.
	echo -e "Calling the final stage"
	
	mass --verbosity=2 --finalInstallStage
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
