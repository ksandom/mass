# Scripts for doing stuff with the filesystem
# This library is automatically included for you if you include includeLibs.sh

function resolveSymlinks
{
	# TODO Check this works as expected on other systems. It should produce output like this:
	#  aws-sdk-for-php	/home/ksandom/files/develop/externalRepos/aws-sdk-for-php/
	
	dirToScan="$1"
	ls -l --time-style=+NODATE "$dirToScan" | tail -n +2 | sed 's/^.*NODATE.//g;s/ -> /	/g' 
}

function getFile
{
	fullPath="$1"
	echo "$fullPath" | sed 's#.*/##g'
}

