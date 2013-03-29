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

function doInstall
{
	startDir=`pwd`
	repoDir="$configDir/repos/$programName"
	if mkdir -p "$configDir/data/hosts" "$binExec" "$bin" "$configDir/repos" "$configDir/externalLibraries"
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
	
	echo "Install details:
	what: $programName
	config: $configDir
	bin: $bin
	binExec: $binExec
	startDir: $startDir
	repoDir: $repoDir
	installType: $installType
	installNotes: $installTypeComments"
	
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
	
	
	if [ "$installType" == 'cp' ]; then
		# echo -e "Making the thing runnable"
		cd $binExec
		cp -R "$startDir/$programName" "$startDir/manageMass" .
		chmod 755 "$bin/$programName" "$bin/manageMass"
	else
		# echo -e "Making the thing runnable"
		cd $binExec
		ln -sf "$startDir/$programName" "$startDir/manageMass" .
	fi
	
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

