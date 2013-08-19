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
	storageDir=$configDir
	binExec=~/bin
}

function rootInstall
{
	# echo "Root install chosen"
	configDir="/etc/$programName"
	storageDir=$configDir
	binExec=/usr/bin
	installType='cp'
	
	if [ -e /root/.mass ]; then
		echo "Legacy root install exists. This will interfere with new installs."
		mv -v /root/.mass /root/.mass.obsolete
	fi
}

function linkedInstall
{
	# echo "Linked install chosen"
	installTypeComments="This is a linked install so you need to keep the repository that you installed from in place. 
	If you don't want to do this, you may want to consider installing as root, which will make it available to all users."
	
	configDir=~/.$programName
	storageDir=$configDir
	binExec=~/bin
	installType='ln'
	
	if [ "`echo $PATH|grep $binExec`" == '' ]; then # A hack for the mac
		binExec=/usr/local/bin
	fi
}

# Choose defaults based on whether we are root or not.
if [ `id -u` -gt 0 ];then
	linkedInstall
else
	rootInstall
fi

# Detect old settings in the right situations.
case  "$1" in
	'--help')
		true
	;;
	'--dontDetect')
		true
	;;
	'--defaults')
		detectOldSettings defaults
	;;
	*)
		detectOldSettings
	;;
esac

# Check parameters for any settings that needs to be set.
checkParameters "$*" $0

# Make it happen
doInstall
