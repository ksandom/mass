# Including this file like so:
#   requiredParms="$1"
#   . `dirname $0`/includeLibs
#
# Will make sure you have everything you need to make a nice utility to support the external administration of mass.
#
# If you don't want to require any parameters set requiredParms to none like so:
#   requiredParms="none"
#
# If the there aren't sufficient parameters, then the script will exit with an error code of 1 and will display the help.

# To include a library, do it like this:
# . $libDir/getRepo




function displayHelp
{
	scriptName=`echo $0|sed 's#^.*/##g'`
	
	tail -n +2 $0 | grep '#'|sed 's/^#/ /g;s/$0/'$scriptName'/g'
}


 # Test if the user has requested something specific.
case $1 in
	'--help'|'-h')
		displayHelp
		exit 0
	;;
esac

 # Test that we have enough imput.
if [ "$requiredParms" == "" ]; then
	echo "Insufficient parameters given, here's some help for you ----v"
	displayHelp
	exit 1
fi



startDir=`pwd`


 # Do stuff. If you want to add something that will get included in all the supplimentary scripts, this is the place to do it.
. `dirname $0`/libs/getMassDetails.sh


cd "$startDir"
