#!/bin/bash
# Install the program
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# If installed using the root user, the program will be available to all users. Otherwise it will be installed locally to the current user.
# Alternatively, if you install it as a non-root user, you can do a linked install like this:
# ./install.sh linked
#
# This is useful for development where you want to test changes without reinstalling.

# install.sh will get replaced eventually. For now it does what I need.

programName='mass'
fileThings='macros modules templates'
directoryThings='packages'
things="$fileThings $directoryThings"
installTypeComments=''

cd `dirname $0`
. supplimentary/libs/installLibs.sh
. supplimentary/libs/packages.sh

function userInstall
{
	# echo "Non root install chosen"
	configDir=~/.$programName
	bin="$configDir/bin"
	binExec=~/bin
	
	doInstall
}

function rootInstall
{
	# echo "Root install chosen"
	configDir="/etc/$programName"
	bin="/usr/bin"
	binExec=/usr/bin
	installType='cp'
	
	doInstall
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

function linkedInstall
{
	# echo "Linked install chosen"
	installTypeComments="This is a linked install so you need to keep the repository that you installed from in place. 
	If you don't want to do this, you may want to consider installing as root, which will make it available to all users."
	
	configDir=~/.$programName
	bin="."
	binExec=~/bin
	installType='ln'
	
	if [ "`echo $PATH|grep $binExec`" == '' ]; then # A hack for the mac
		binExec=/usr/local/bin
	fi
	
	doInstall
}






function removeObsoleteStuff
{
	mkdir -p $configDir/obsolete
	for thing in $things;do
		mv $configDir/$thing* $configDir/obsolete 2>/dev/null
	done
	
	# mv $configDir/obsolete/*available $configDir
	
	if [ `ls $configDir/obsolete 2> /dev/null | wc -l` -lt 1 ]; then
		rmdir $configDir/obsolete
	else
		echo "removeObsoleteStuff: Obsolete stuff has been put in $configDir/obsolete. It is likely that this directory can simply be deleted. But if you have done any custom work, you will want to check that it isn't here first." | tee $configDir/obsolete/readme.md
	fi
}

function doInstall
{
	startDir=`pwd`
	repoDir="$configDir/repos/$programName"
	mkdir -p "$configDir/data/hosts" "$binExec" "$bin" "$configDir/repos" "$configDir/externalLibraries"
	
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
		cp -Rv "$startDir" "$configDir/repos"
	else
		cd "$configDir/repos"
		ln -sf "$startDir" .
	fi
	
	cd "$configDir"
	# echo -e "Linking like there's no tomorrow."
	ln -sf "$repoDir"/docs "$repoDir/core.php" "$repoDir/examples" "$repoDir"/interfaces "$repoDir"/supplimentary .
	
	# "$repoDir"/modules-*available "$repoDir"/macros-*available "$repoDir"/templates-*available "$repoDir"/packages-*available
	
	ln -sf "$repoDir/index.php" .
	
	# echo -e "Setting up remaining directory structure"
	mkdir -p config data/1LayerHosts
	
	
	if [ "$installType" == 'cp' ]; then
		# echo -e "Making the thing runnable"
		cd $binExec
		cp -Rv "$startDir/$programName" "$bin"
		chmod 755 "$bin/$programName"
	else
		# echo -e "Making the thing runnable"
		cd $binExec
		ln -sf "$startDir/$programName" .
	fi
	
	createProfile commandLine
	enableEverythingForProfile commandLine mass 
	cleanProfile commandLine

	createProfile privateWebAPI
	enableEverythingForProfile privateWebAPI mass 
	disableItemInProfile privateWebAPI packages SSH
	cleanProfile privateWebAPI

	cloneProfile privateWebAPI publicWebAPI
	disableItemInProfile publicWebAPI packages AWS
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
	
	mass -vv --finalInstallStage
}

if [ `id -u` -gt 0 ];then
	linkedInstall
else
	rootInstall
fi
 
