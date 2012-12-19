# Get mass details
# See documentation inside includeLibs.sh to use this library

if [ "$configDir" == '' ]; then
	export configDir=`mass --get=General,configDir --singleStringNow --null`
fi

if [ "$libDir" == '' ]; then
	export libDir="$configDir/supplimentary/libs"
fi

if [ "$supplimentaryDir" == '' ]; then
	export supplimentaryDir="$configDir/supplimentary"
fi

managementTool="manageMass"
