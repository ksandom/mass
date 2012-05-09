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

function userInstall
{
	echo "Non root install chosen"
	configDir=~/.$programName
	bin="$configDir/bin"
	binExec=~/bin
	
	doInstall
}

function rootInstall
{
	echo "Root install chosen"
	configDir="/etc/$programName"
	bin="/usr/bin"
	binExec=/usr/bin
	installType='cp'
	
	doInstall
}

function checkPrereqs
{
	for prereq in php;do
		if ! which $prereq; then
			echo "Could not find $prereq in \$PATH" >&2
			exit 1
		fi
	done
}

function linkedInstall
{
	echo "Linked install chosen"
	configDir=~/.$programName
	bin="."
	binExec=~/bin
	installType='ln'
	
	if [ "`echo $PATH|grep $binExec`" == '' ]; then # A hack for the mac
		binExec=/usr/local/bin
	fi
	
	doInstall
}

function doInstall
{
	startDir=`pwd` # for some reason ~- wasn't working
	mkdir -p "$configDir/data/hosts" "$binExec" "$bin"
	
	checkPrereqs
	
	echo "Install details:
	What: $programName
	Where:
		config: $configDir
		bin: $bin
		binExec: $binExec
		startDir: $startDir
		installType: $installType"
	
	if [ "$installType" == 'cp' ]; then
		echo -e "\n# Copying available stuff"
		cp -Rv docs modules-* core.php macros-* examples "$configDir"
		
		echo -e "\n# Setting up remaining directory structure"
		cd "$configDir"
		mkdir -p modules-enabled macros-enabled templates-enabled config
		
		echo -e "\n# Making the thing runnable"
		cd $binExec
		cp -Rv "$startDir/$programName" "$bin"
		chmod 755 "$bin/$programName"
		
		for thing in macros modules templates;do
			echo -e "\n# Sorting out $thing available/enabled"
			cd "$configDir/$thing-enabled"
			ln -sf ../$thing-available/* .
		done
	else
		cd "$configDir"
		
		echo -e "\n# Linking like there's no tomorrow."
		ln -sfv "$startDir"/docs "$startDir"/modules-*available "$startDir"/macros-*available "$startDir"/templates-*available "$startDir/core.php" "$startDir/examples" . 
		
		echo -e "\n# Setting up remaining directory structure"
		mkdir -p modules-enabled macros-enabled templates-enabled config
		
		echo -e "\n# Making the thing runnable"
		cd $binExec
		ln -sfv "$startDir/$programName" .
		
		for thing in macros modules templates;do
			echo -e "\n# Sorting out $thing available/enabled"
			cd "$configDir/$thing-enabled"
			ln -sf ../$thing-available/* .
		done
	fi
	
	echo -e "\n# Cleanup"
	rm -f "$configDir/macros-enabled/example"*
	rm -f "$configDir/modules-enabled/example"
	rm -f "$configDir/templates-enabled/example"
	
	if [ ! -f "$configDir/config/Credentials.config.json" ];then
		echo -e "\n# First time setup"
		mass --set=Credentials,defaultKey,id_rsa --saveStoreToConfig=Credentials
	fi
	
	# It should be safe to do this on an existing setup.
	echo -e "\n# Detecting stuff"
	mass --createDefaultValues
	mass --detect=Terminal,seed,GUI --saveStoreToConfig=Terminal
}

if [ `id -u` -gt 0 ];then
	case $1 in
		'linked')
			linkedInstall
		;;
		*)
			echo "User install is broken at the moment and is low on my priorities to fix. Feel free to fix it."
			echo "In the mean time we'll use the linked install. You can install from another location by simply running $0 from that location at any time."
			echo
			echo "Alternatively you can comment out the linkedInstall and exit and roll the dice ;)"
			echo
			echo "Continuing with linked install."
			
			
			linkedInstall
			exit 1
			userInstall
		;;
	esac
else
	rootInstall
fi
 
