# Display stuff nicely

function tabsToSpacedDashes
{
	if [ "$noFormat" == '' ]; then
		sed 's/	/ - /g'
	else
		cat -
	fi
}

function testInput
{
	# TODO This function can be dramatically improved by using bash's builtin functionality.
	
	userInput="$1"
	possibleOptions="$2"
	
	if [ "$userInput" == '' ]; then
		return 1
	else
		if [ "`echo \"$possibleOptions\" | grep $userInput`" ]; then
			return 0
		else
		return 1
		fi
	fi
}

function applyDefault
{
	# TODO This function can be dramatically improved by using bash's builtin functionality.
	inputToTest="$1"
	defaultValue="$2"
	if [ "$inputToTest" == '' ]; then
		echo "$defaultValue"
	else
		echo "$inputToTest"
	fi
}

function confirm
{
	# TODO This function can be dramatically improved by using bash's builtin functionality.
	
	message="$1"
	default=${2:-'n'}
	match=${3:-'y'}
	input="$4"
	options='y n'
	
	while ! testInput "$input" "$options"; do
		echo -n "$message ($options)[$default]: "
		read input
		input=`applyDefault "$input" "$default"`
	done
	
	if [ "$input" == "$match" ]; then
		return 0
	else
		return 1
	fi
}

