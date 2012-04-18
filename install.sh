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
	
	if [ "`echo $PATH|grep $binExec`" == '' ]; then # A hack for the mac
		binExec=/usr/local/bin
	fi
	
	doInstall
}

function doInstall
{
	startDir=`pwd` # for some reason ~- wasn't working
	mkdir -p "$configDir/data/hosts" "$binExec" "$bin"
	
	echo "Install details:
	What: $programName
	Where:
		config: $configDir
		bin: $bin
		binExec: $binExec
		startDir: $startDir"
	
	if [ "$bin" != '.' ]; then
		cp -Rv docs modules-* core.php macros-* examples "$configDir"
		cd "$configDir"
		mkdir -p modules-enabled macros-enabled templates-enabled config
		cp -Rv $programName "$bin"
		cd $binExec
		pwd
		ln -sfv "$bin/$programName" .
		chmod 755 "$bin/$programName"
		cd "$configDir/macros-enabled"
		ln -sf ../macros-available/* .
		cd "$configDir/modules-enabled"
		ln -sfv ../modules-available/* .
		cd "$configDir/templates-enabled"
		ln -sf ../templates-available/* .
	else
		cd "$configDir"
		ln -sfv "$startDir"/docs "$startDir"/modules-*available "$startDir"/macros-*available "$startDir"/templates-*available "$startDir/core.php" "$startDir/examples" . 
		mkdir -p modules-enabled macros-enabled templates-enabled config
		cd $binExec
		ln -sfv "$startDir/$programName" .
		cd "$configDir/macros-enabled"
		ln -sfv ../macros-available/* .
		cd "$configDir/modules-enabled"
		ln -sfv ../modules-available/* .
		cd "$configDir/templates-enabled"
		ln -sfv ../templates-available/* .
	fi
	
	# First time setup
	if [ ! -f "$configDir/config/Credentials.config.json" ];then
		mass --set=Credentials,defaultKey,id_rsa --saveStoreToConfig=Credentials
	fi
	
	# Detect stuff. It should be safe to do this on an existing setup.
	mass --createDefaultValues
	mass --detect=Terminal,seed,GUI --saveStoreToConfig=Terminal
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
 
