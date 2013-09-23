# Get a repo
# Install/update a repo

function getRepo
{
	repoName="$1" # What we are going to refer to it as once it's installed.
	repoSrc="$2" # Where to get it from. This is likely to be a git URL.
	reposDir="$configDir/repos"
	repoDir="$reposDir/$repoName"
	
	if [ -d "$repoDir"  ]; then
		cd "$repoDir"
		git pull
	else
		mkdir -p "$reposDir"
		git clone "$repoSrc" "$repoDir"
	fi
}

function addPretendRepo
{
	# This is specifically for the rare situation where you need to link in an 
	# existing folder structure as if it was a cloned repo.
	# 
	# The only situation I can think of where it is valid to use this is if you
	# are developing that repo.
	
	# Sort out what we are working with.
	fullSrcPath="$1"
	name=`echo "$fullSrcPath" | sed 's#/$##;
		s#.*/##g'`
	if [ "$2" != "" ]; then
		dstName="$2"
	else
		dstName="$name"
	fi

	# Prep
	mkdir /tmp/$$
	cd /tmp/$$

	# Get the link
	ln -s "$fullSrcPath" .
	if [ "$name" != "$dstName" ]; then
		mv "$name" "$dstName"
	fi 

	# Pre clean
	cd "$configDir"/repos
	if [ -f "$dstName" ]; then
		rm "$dstName"
	elif [ -d "$dstName" ]; then
		rm -Rf "$dstName"
	elif [ -L "$dstName" ]; then
		rm "$dstName"
	fi


	# Put it in place
	mv "/tmp/$$/$dstName" .

	# Clean up
	rm -Rf /tmp/$$
}
