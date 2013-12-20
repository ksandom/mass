# Package management

function cleanEnabled
{
	testDir="$2"
	testFunction="$1"
	profileName="$3"
	
	if cd "$testDir"; then
		for item in *;do
			if ! $testFunction "$item"; then
				echo "$item is no longer present. Disabling in profile \"$profileName\"."
				rm "$item"
			fi
			cd "$testDir"
		done
	else
		echo
		echo "Mass install: cleanEnabled: WARNING Clean aborted since we could not sucessfully get into the directory to be cleaned. Continuing would be insanity. Please fix this.";
		echo "	pwd: 		`pwd`"
		echo "	testDir:		$testDir"
		echo "	testFunction:	$testFunction"
		echo "	profileName:	$profileName"
		echo "	configDir:	$configDir"
		echo
	fi
}

function testEnabledFile
{
	item="$1"
	cat "$item" 1>/dev/null 2>&1
	return $?
}

function testEnabledDirectory
{
	item="$1"
	cd "$item" 1>/dev/null 2>&1
	return $?
}

function createProfile
{
	name="$1"
	
	# TODO add protection for invalid fileNaming
	
	createBareProfile "$name"
	
	# TODO This should probably be abstracted out to an "all" profile that includes these files for all profiles.
	enableItemInProfile "$name" 'modules' 'data.php' 'mass'
	enableItemInProfile "$name" 'modules' 'macro.php' 'mass'
	enableItemInProfile "$name" 'modules' 'packages.php' 'mass'
	enableItemInProfile "$name" 'modules' 'template.php' 'mass'
	
	doExec='true'
	for parm in "$@"; do
		case $parm in
			'--noExec')
				doExec='false'
			;;
		esac
	done
	
	if [ "$doExec" == 'true' ]; then
		createExec "$name"
	fi
}

function removeProfile
{
	name="$1"
	removeBareProfile "$name"
}


function createExec
{
	name="$1"
	if [ ! "$2" == '' ]; then 
		export programName="$2"
	else
		programName="$name"
	fi
	cd "$binExec"
	srcFile="${languageRepo:-$startDir}/src/exec"
	
	copyTemplatedFile "$srcFile" "$name"
	chmod 755 "$name"
}

function removeExec
{
	name="$1"
	
	cd "$binExec"
	if [ ! "$name" == '' ] && [ -e "$binExec/$name" ]; then
		rm "$name"
	else
		echo "removeExec: Could not find \"$name\"" >&2
		return 1
	fi
	
	
}

function userRemoveExec
{
	name="$1"
	
	cd "$binExec"
	if [ "`ls -1 "$configDir"/profiles | grep \"^$name$\"`" != '' ]; then
		removeExec "$name"
	else
		echo "There is no profile by this name. Although the profile and the exec are not intrinsicly linked, it would be a bad idea to let someone delete any string which we don't know about. Your easies t next step is to create a profile and then run removeProfile, which will call removeExec."
	fi
}


function createBareProfile
{
	name="$1"
	mkdir -p $configDir/profiles/$name/{packages,modules,macros,templates}
}

function removeBareProfile
{
	name="$1"
	profileToRemove="$configDir/profiles/$name"
	if [ -d "$profileToRemove" ]; then
		rm -Rf "$profileToRemove"
	elif [ -h "$profileToRemove" ]; then
		rm -f "$profileToRemove"
	else
		echo "Could not find a profile called \"$name\". I looked in \"$profileToRemove\""
	fi
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
				# TODO Potentially this can be refactored to use enableItemInProfile
				if [ "$thing" == 'packages' ]; then
					if [ -e "$repo-$item" ]; then
						true
					elif [ ! -e $item ]; then
						ln -sf "$configDir/repos/$repo/$thing-available/$item" .
						mv "$item" "$repo-$item"
					elif [ "$repo" == 'mass' ]; then # Migrate old naming to new naming.
						mv "$item" "$repo-$item"
					fi
				else
					ln -sf "$configDir/repos/$repo/$thing-available/$item" .
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
	repo="$4"
	
	itemPath="$configDir/repos/$repo/$itemType-available/$item"
	
	cd $configDir/profiles/$profile/$itemType
	if [ -e "$itemPath" ]; then
		ln -sf "$itemPath" .
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
	
	cd "$configDir/profiles"
	rm -Rf "$to"
	cp -R "$from" "$to"
}

function cleanProfile
{
	name="$1"
	
	for thing in $fileThings;do
		cleanEnabled testEnabledFile "$configDir/profiles/$name/$thing" "$name"
	done
	
	for thing in $directoryThings;do
		cleanEnabled testEnabledDirectory "$configDir/profiles/$name/$thing" "$name"
	done
}

function obsoleteProfile
{
	name="$1"
	
	cd "$configDir/profiles"
	if [ -e "$name" ]; then
		mkdir -p "$configDir/obsolete/profiles"
		mv -v "$name" "$configDir/obsolete/profiles"
	fi
}

function enabledPacakge
{
	#     repoName is the named repository. (found with repoList)
	#     packageRegex is a regular expression to match packages found with packageList.
	#     profileNameRegex is a regular expression to match profiles found with profileList.
	repoName="$1"
	packageRegex="$2"
	profileNameRegex="$3"

	if [ ! -e "$configDir/repos/$repoName" ]; then
		echo "Did not find a repo matching \"$repoName\""
		exit 1
	fi

	profiles=`$managementTool profileList | grep "$profileNameRegex"`
	if [ "$profiles" == '' ]; then
		echo "Did not match any profiles using regex \"$profileNameRegex\""
		exit 1
	fi

	while read profile; do
		while read package;do
			# TODO test this
			packagePath="$configDir/repos/$repoName/packages-available/$package"
			profilePath="$configDir/profiles/$profile/packages"
			cd "$profilePath"
			
			if [ -e "$package" ]; then
				echo "Obsolete named package \"$package\" in \"$packagePath\". It should be in the form repoNanme-packageName."
			elif [ ! -e "$repoName-$package" ]; then
				ln -sf "$packagePath" .
				mv "$package" "$repoName-$package"
			fi
		done < <($managementTool repoListPackages $repoName --short | grep "$packageRegex")
	done < <(echo "$profiles")
}

function disablePackage
{
	#     repoName is the named repository. (found with repoList)
	#     packageRegex is a regular expression to match packages found with packageList.
	#     profileNameRegex is a regular expression to match profiles found with profileList.
	repoName="$1"
	packageRegex="$2"
	profileNameRegex="$3"

	profiles=`$managementTool profileList | grep "$profileNameRegex"`
	if [ "$profiles" == '' ]; then
		echo "Did not match any profiles using regex \"$profileNameRegex\""
		exit 1
	fi

	while read profile; do
		while read package;do
			packagePath="$configDir/profiles/$profile/packages/$repoName-$package"
			if [ -h "$packagePath" ]; then
				rm -f "$packagePath"
			elif [ -d "$packagePath" ]; then
				echo "$packagePath is a directory. Is this your only copy of your work?! This should be a symlink."
			fi
		done < <($managementTool repoListPackages $repoName --short | grep "$packageRegex")
	done < <(echo "$profiles")
}