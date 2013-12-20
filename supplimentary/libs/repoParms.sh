# Manipulate application repository parameters.

function repoSetParm
{
	repoName="$1"
	parameterName="$2"
	value="$3"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	touch "$parmFile"
	
	mass --collectionLoadArbitrary=RepoParms,"$parmFile" --setNested="RepoParms,$parameterName,$value"
}

function repoGetParm
{
	repoName="$1"
	parameterName="$2"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		mass --collectionLoadArbitrary=RepoParms,"$parmFile" --get="RepoParms,$parameterName" -s
	fi
}

function repoRemoveParm
{
	repoName="$1"
	parameterName="$2"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		mass --collectionLoadArbitrary=RepoParms,"$parmFile" --unset="RepoParms,$parameterName"
	fi
}

function repoGetParms
{
	# TODO write a data version of this
	repoName="$1"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		mass --collectionLoadArbitrary=RepoParms,"$parmFile" --getCategory="RepoParms"
	fi
}

function repoGetParmPackages
{
	repoName="$1"
	
	parmFile="$configDir/repos/$repoName/parameters.json"
	
	if [ -f "$parmFile" ]; then
		mass --collectionLoadArbitrary=RepoParms,"$parmFile" --retrieveResults="RepoParms,packages" --flatten --toString="~%sourceRepo%~ ~%packageRegex%~" -s
	fi
}

function showReposWithParms
{
	ls -1 "$configDir"/repos | while read repoName;do
		if [ -f ""$configDir"/repos/$repoName/parameters.json" ]; then
			echo "$repoName"
		fi
	done
}
