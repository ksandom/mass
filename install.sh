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
	bin="/usr/bin/$programName"
	binExec=/usr/bin
	
	doInstall
}

function linkedInstall
{
	echo "Linked install chosen"
	configDir=~/.$programName
	bin="."
	binExec=~/bin
	
	doInstall
}

function doInstall
{
	startDir=`pwd` # for some reason ~- wasn't working
	mkdir -p "$configDir/data/hosts" "$binExec" "$bin"
	if [ "$bin" != '.' ]; then
		cp -Rv modules-* core.php macros-* "$configDir"
		cd "$configDir"
		mkdir -p modules-enabled macros-enabled templates-enabled
		cp -Rv $programName "$bin"
		cd $binExec
		pwd
		ln -sfv "$bin/$programName" .
		chmod 755 "$bin/$programName"
		cd "$configDir/macros-enabled"
		ln -sf ../macros-available/* .
		cd "$configDir/templates-enabled"
		ln -sf ../templates-available/* .
	else
		cd "$configDir"
		ln -sfv "$startDir"/modules-*available "$startDir"/macros-*available "$startDir"/templates-*available "$startDir/core.php" . 
		mkdir -p modules-enabled macros-enabled templates-enabled
		cd $binExec
		ln -sfv "$startDir/$programName" .
		cd "$configDir/macros-enabled"
		ln -sf ../macros-available/* .
	fi
	
}

if [ `id -u` -gt 0 ];then
	case $1 in
		'linked')
			linkedInstall
		;;
		*)
			echo "This is broken at the moment and is low on my priorities to fix. Feel free to fix it."
			echo "I suggest that you run './install linked' as this will be most useful to people at the moment."
			echo "Alternatively you can comment out the exit and roll the dice ;)"
			exit 1
			userInstall
		;;
	esac
else
	rootInstall
fi
 
