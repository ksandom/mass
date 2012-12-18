function displayHelp
{
	if [ "$scriptName" == '' ]; then
		scriptName=`echo $0|sed 's#^.*/##g'`
	fi
	
	tail -n +2 $0 | grep '^#'|sed 's/^#/ /g;s/$0/'"$scriptName"'/g'
	
	if [ "$extraHelp" != '' ]; then
		"$extraHelp"
	fi
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
