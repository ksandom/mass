# Get mass details
# See documentation inside includeLibs.sh to use this library

if [ "$configDir" == '' ]; then
	configDir=`mass --get=General,configDir --singleStringNow --null`
fi

if [ "$libDir" == '' ]; then
	libDir="$configDir/supplimentary/libs"
fi

if [ "$supplimentaryDir" == '' ]; then
	supplimentaryDir="$configDir/supplimentary"
fi
