# Get a repo
# Install/update a repo

function getRepo
{
	repoName="$1" # What we are going to refer to it as once it's installed.
	repoSrc="$2" # Where to get it from. This is likely to be a git URL.
	libDir="$configDir/externalLibraries"
	repoDir="$libDir/$repoName"
	
	if [ -d "$repoDir"  ]; then
		cd "$repoDir"
		git pull
	else
		mkdir -p "$libDir"
		git clone "$repoSrc" "$repoDir"
	fi
}

