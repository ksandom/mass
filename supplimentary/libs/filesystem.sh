# Scripts for doing stuff with the filesystem
# This library is automatically included for you if you include includeLibs.sh

function resolveSymlinks
{
	dirToScan="$1"
	ls -l --time-style=+NODATE "$dirToScan" | tail -n +2 | sed 's/^.*NODATE.//g;s/ -> /	/g' 
}

