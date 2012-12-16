# Package management

function cleanEnabled
{
	testDir="$2"
	testFunction="$1"
	
	cd "$testDir"
	for item in *;do
		if ! $testFunction "$item"; then
			echo "$item is no longer present. Disabling."
			rm "$item"
		fi
		cd "$testDir"
	done
}

function testEnabledFile
{
	item="$1"
	cat "$item" 2>&1 > /dev/null
	return $?
}

function testEnabledDirectory
{
	item="$1"
	cd "$item" 2>&1 > /dev/null
	return $?
}

function createProfile
{
	name="$1"
	mkdir -p $configDir/profiles/$name/{packages,modules,macros,templates}
}

function enableEverythingForProfile
{
	name="$1"
	repo=${2:-mass}
	
	start=`pwd`
	for thing in $things; do
		cd $configDir/profiles/$name/$thing
		if [ `ls $configDir/repos/$repo/$thing-available|wc -l 2> /dev/null` -gt 0 ]; then
			while read item;do
				if [ ! -e $item ]; then
					ln -sf $configDir/repos/$repo/$thing-available/$item .
				fi
			done < <(ls $configDir/repos/$repo/$thing-available)
		fi
	done
	
	cd $start
}

function enableItemInProfile
{
	profile="$1"
	itemType="$2" # package,module,macro,template
	item="$3" # AWS,SSH
	
	cd $configDir/profiles/$profile/$itemType
	if [ -e $configDir/profiles/$profile/$itemType/$item ]; then
		ln -sf $configDir/profiles/$profile/$itemType/$item .
	else
		echo "enableItemInProfile: Could not find $configDir/profiles/$profile/$itemType/$item" 1>&2
	fi
}

function disableItemInProfile
{
	profile="$1"
	itemType="$2" # package,module,macro,template
	item="$3" # AWS,SSH
	
	cd $configDir/profiles/$profile/$itemType
	if [ -e $configDir/profiles/$profile/$itemType/$item ]; then
		rm $configDir/profiles/$profile/$itemType/$item
	else
		echo "disableItemInProfile: Could not find $configDir/profiles/$profile/$itemType/$item" 1>&2
	fi
}

function cloneProfile
{
	from="$1"
	to="$2"
	
	cd $configDir/profiles
	rm -Rf "$to"
	cp -R "$from" "$to"
}

function cleanProfile
{
	name="$1"
	
	for thing in $fileThings;do
		cleanEnabled testEnabledFile "$configDir/profiles/$name/$thing"
	done
	
	for thing in $directoryThings;do
		cleanEnabled testEnabledDirectory "$configDir/profiles/$name/$thing"
	done
}
