# Get mass details
# See documentation inside includeLibs.sh to use this library

if [ "$configDir" == '' ]; then
	export configDir=`mass --get=General,configDir --singleStringNow --null`
fi

libDir="$configDir/supplimentary/libs"
supplimentaryDir="$configDir/supplimentary"
profileDir="$configDir/profiles"

managementTool="manageMass"

for parameter in $@;do
	case $parameter in
		'--short')
			short=true
		;;
	esac
done
