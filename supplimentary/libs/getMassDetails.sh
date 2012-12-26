# Get mass details
# See documentation inside includeLibs.sh to use this library

function isValue
{
	if [ "$1" == '' ]; then
		return 1 # false
	fi
	
	if [ "${1:0:2}" != '--' ];then
		return 0 # true
	else
		return 1 # false
	fi
}

if [ "$configDir" == '' ]; then
	export configDir=`mass --get=General,configDir --singleStringNow --null`
fi

libDir="$configDir/supplimentary/libs"
supplimentaryDir="$configDir/supplimentary"
profileDir="$configDir/profiles"
repoDir="$configDir/repos"

managementTool="manageMass"

for parameter in $@;do
	case $parameter in
		'--short')
			short=true
		;;
		'--noFormat')
			noFormat=true
		;;
	esac
done
