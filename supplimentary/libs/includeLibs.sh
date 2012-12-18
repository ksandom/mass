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


startDir=`pwd`


 # Do stuff. If you want to add something that will get included in all the supplimentary scripts, this is the place to do it.
. `dirname $0`/libs/getMassDetails.sh
. "$libDir/help.sh"
. "$libDir/filesystem.sh"
. "$libDir/display.sh"


cd "$startDir"
