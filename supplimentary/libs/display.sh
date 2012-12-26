# Display stuff nicely

function tabsToSpacedDashes
{
	if [ "$noFormat" == '' ]; then
		sed 's/	/ - /g'
	else
		cat -
	fi
}
