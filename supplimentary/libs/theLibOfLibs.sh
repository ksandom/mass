# Stuff for managing the libraries and supplimentary scripts.

function getListOfSupplimeentaryScripts
{
	cd "$supplimentaryDir"
	while read file;do
		if [ -f "$file" ]; then
			description=`grep -A 1 "Description" "$file" | tail -n 1 | sed 's/# *//g'`
			echo "$name $file	$description"
		fi
	done < <(ls -1)
}

function displayListOfSupplimeentaryScripts
{
	getListOfSupplimeentaryScripts | sed 's/	/ - /g;s/^/   /g'
}