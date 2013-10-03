# Manipulate application repository parameters.

function repoSetParm
{
	repoName="$1"
	parameterName="$2"
	value="$3"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	touch "$parmFile"
	
	mass --collectionLoadArbitrary=RepoParms,"$parmFile" --set="RepoParms,$parameterName,$value"
}

function repoGetParm
{
	repoName="$1"
	parameterName="$2"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	touch "$parmFile"
	
	mass --collectionLoadArbitrary=RepoParms,"$parmFile" --get="RepoParms,$parameterName"
}

function repoGetParms
{
	repoName="$1"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	touch "$parmFile"
	
	mass --collectionLoadArbitrary=RepoParms,"$parmFile" --getCategory="RepoParms" # TODO finish this
}

function showReposWithParms
{
	ls -1 "$configDir"/repos | while read repoName;do
		if [ -f ""$configDir"/repos/$repoName/parameters.json" ]; then
			echo "$repoName"
		fi
	done
}
