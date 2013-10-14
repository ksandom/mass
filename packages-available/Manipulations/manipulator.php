<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manipulate output
class Manipulator extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Manipulator');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('toString'), 'toString', 'Convert array of arrays into an array of strings. eg --toString="blah file=%hostName% ip=~%externalIP%~"', array('array', 'string'));
				$this->core->registerFeature($this, array('f', 'flatten'), 'flatten', 'Flatten an array of arrays into a keyed array of values. --flatten[=limit] (default:-1). Note that "limit" specifies how far to go into the nesting before simply returning what ever is below. Choosing a negative number specifies how many levels to go in before beginning to flatten. Choosing 0 sets no limit.', array('array', 'string'));
				$this->core->registerFeature($this, array('finalFlatten'), 'finalFlatten', 'To be used after a --flatten as gone as far as it can.', array('array', 'string'));
				$this->core->registerFeature($this, array('replace'), 'replace', 'Replace a pattern matching a regular expression and replace it with something defined. --replace=searchRegex,replacement', array('array', 'string'));
				$this->core->registerFeature($this, array('unique'), 'unique', 'Only keep unique entries. The exception is non-string values will simply be kept without being compared.', array('array', 'string'));
				$this->core->registerFeature($this, array('requireEach', 'refine'), 'requireEach', 'Require each entry to match this regular expression. --requireEach=regex', array('array', 'result'));
				$this->core->registerFeature($this, array('recursiveRequireEach', 'recursiveRefine'), 'recursiveRequireEach', 'Require each entry to match this regular expression somewhere in its dataset. --requireEach=regex', array('array', 'result'));
				$this->core->registerFeature($this, array('requireItem'), 'requireItem', 'Require a named entry in each of the root entries. A regular expression can be supplied to provide a more precise match. --requireItem=entryKey[,regex]', array('array', 'result'));
				$this->core->registerFeature($this, array('excludeEach', 'exclude'), 'excludeEach', 'The counterpart of --requireEach. Excludes any item that contains an entry that matches the regular expression. --requireEach=regex', array('array', 'result'));
				$this->core->registerFeature($this, array('recursiveExcludeEach', 'recursiveExclude'), 'recursiveExcludeEach', 'The counterpart of --recursiveRequireEach. Excludes any item that contains an entry that matches the regular expression somewhere in the dataset. --requireEach=regex', array('array', 'result'));
				$this->core->registerFeature($this, array('excludeItem'), 'excludeItem', 'The counterpart of --requireItem. Excludes any items wherre a named entry matches the specified regex. --excludeItem=entryKey[,regex]', array('array', 'result'));
				$this->core->registerFeature($this, array('manipulateEach'), 'manipulateEach', 'Call a feature for each entry in the result set that contains an item matching this regular expression. --manipulateEach=regex,feature featureParameters', array('array', 'result'));
				$this->core->registerFeature($this, array('manipulateItem'), 'manipulateItem', 'Call a feature for each entry that contains an item explicity matching the one specified. --manipulateItem=entryKey,regex,feature featureParameters', array('array', 'result'));
				$this->core->registerFeature($this, array('chooseFirst'), 'chooseFirst', 'Choose the first non-empty value and put it into the destination variable. --chooseFirst=dstVarName,srcVarName1,srcVarName2[,srcVarName3[,...]]', array('array', 'result'));
				$this->core->registerFeature($this, array('chooseFirstSet'), 'chooseFirstSet', "Choose the first set whose key has a non-empty value and put each item in the set into the it's destination variable. --chooseFirstSet=setSize,srcVarName1,dstVarName1,srcVarName2,dstVarName2[,srcVarName3,dstVarName3[,...]] . The setSize determines how many src/dst pairs are in each set. eg --chooseFirstSet=3,x,y,z,a1,b1,c1,a2,b2,c2 . In this example, we define that the setSize is 3. Therefore we can take a,b and c from set 1 and 2 and put it into x, y, and z. In each case 'a' is the variable that will be tested. So if a1 is empty, a2 will be tested. If that succeeds then a2, b2 and c3 will be put into x, y and z.", array('array', 'result'));
				$this->core->registerFeature($this, array('chooseFirstSetIfNotSet'), 'chooseFirstSetIfNotSet', "See --chooseFirstSet for full help. This variant will only take action on each result that doesn't have x set.", array('array', 'result'));
				$this->core->registerFeature($this, array('resultSet'), 'resultSet', 'Set a value in each result item. --setResult=dstVarName,value . Note that this has no counter part as you can already retrieve results with ~%varName%~ and many to one would be purely random.', array('array', 'result'));
				$this->core->registerFeature($this, array('resultSetIfNotSet'), 'resultSetIfNotSet', 'Set a value in each result item only if it is not already set. --resultSetIfNotSet=dstVarName,value . Note that this has no counter part as you can already retrieve results with ~%varName%~ and many to one would be purely random.', array('array', 'result'));
				$this->core->registerFeature($this, array('resultUnset'), 'resultUnset', 'Delete a value in each result item. --resultUnset=dstVarName.', array('array', 'result'));
				$this->core->registerFeature($this, array('addSlashes'), 'addSlashes', 'Put extra backslashes before certain characters to escape them to allow nesting of quoted strings. --addSlashes=srcVar,dstVar', array('array', 'escaping', 'result'));
				$this->core->registerFeature($this, array('cleanUnresolvedResultVars'), 'cleanUnresolvedResultVars', 'Clean out any result variables that have not been resolved. This is important when a default should be blank.', array('array', 'escaping', 'result'));
				$this->core->registerFeature($this, array('take'), 'take', 'Take only a single key from each entry in a result set --take=key.', array('array', 'result'));
				$this->core->registerFeature($this, array('takeSubResult'), 'takeSubResult', 'Take only a single entry in a result set and make that the entrie resultSet. --takeSubResult=key.', array('array', 'result'));
				$this->core->registerFeature($this, array('duplicate', 'dup'), 'duplicate', 'Duplicate the result set. --duplicate[=numberOfTimesToDuplicate]Eg --duplicate=3 would take a result set of [a,b,c] and give [a,b,c,a,b,c,a,b,c]. The original intended use for this was to open extra terminals for each host when using --term or --cssh. Note that --dup --dup is not the same as --dup=2 !', array('array', 'result'));
				$this->core->registerFeature($this, array('count'), 'count', 'Replace the reseulSet with the count of the resultSet. --count', array('result'));
				$this->core->registerFeature($this, array('countToVar'), 'countToVar', 'Count the number of results and stick the answer in a variable. --countToVar=CategoryName,variableName', array('result'));
				$this->core->registerFeature($this, array('pos'), 'pos', 'Insert the position of each result to that result. This can be used simply to track results as they get processed in other ways, or for creating an inprovised unique number for each result (NOTE that that number will not necessarily stay with the same result on subsequent runs if the input result set has changed). --pos[=resultVariableName[,offset]] . resultVariableName defaults to "pos" and offset defaults to "0"', array('result'));
				$this->core->registerFeature($this, array('chooseBasedOn'), 'chooseBasedOn', 'For each item in the result set, choose the value of an array based on the modulous of a named value in the result set and the number of items in the array. This would naturally work well with --pos. --chooseBasedOn=inputValueName,outputValueName,inputCategory[,inputValueName,[subInputValueName,[etc,[etc]]]]', array('result'));
				$this->core->registerFeature($this, array('crc'), 'crc', "For each item in the result set, calculate the CRC of a specified value and set a specified value to that CRC. --crc=inputValueName,outputValueName . Please see the warning on http://uk3.php.net/crc32 for information about it's acuracy. You may want to check out --positiveCRC which is good enough for what I want.", array('result', 'crc'));
				$this->core->registerFeature($this, array('positiveCRC'), 'positiveCRC', "For each item in the result set, calculate the CRC of a specified value and set a specified value to that CRC. --positiveCRC=inputValueName,outputValueName . If the result is negative, take the absolute value. Please see the warning on http://uk3.php.net/crc32 for why this is useful. Note that this output may not be consistent with other applications generating a CRC. If you need that consistency, then you probably want --crc.", array('result', 'crc'));
				
				$this->core->registerFeature($this, array('firstResult', 'firstResults', 'first'), 'firstResult', "Take the first x results, where x is one if not specified. --firstResult[=x]", array('result'));
				$this->core->registerFeature($this, array('lastResult', 'lastResults', 'last'), 'lastResult', "Take the last x results, where x is one if not specified. --lastResult=x", array('result'));
				$this->core->registerFeature($this, array('offsetResult', 'offsetResults'), 'offsetResult', "After x results, take the first y results. --offsetResult=x,y . If y is negative, The results will be taken from the end rather than the beginning. In this case x therefore is an offset from the end, not the beginning.", array('result'));
				$this->core->registerFeature($this, array('keyOn'), 'keyOn', "Key items in the resultSet using a named value from each item in the resultSet. --keyOn=valueName", array('result'));
				$this->core->registerFeature($this, array('keyValueOn'), 'keyValueOn', "Key items in the value of each item in the resultSet using a named value from each item inside that item in the resultSet. If this sounds confusing, just think of it as running --keyOn inside a value inside each item in the result set. --keyValueOn=valueName,subValueName", array('result'));
				$this->core->registerFeature($this, array('lessThan'), 'lessThan', "Restrict the resultset to items where a named result value is less than a specified value. --lessThan=valueName,valueToTest", array('result'));
				$this->core->registerFeature($this, array('greaterThan'), 'greaterThan', "Restrict the resultset to items where a named result value is greater than a specified value. --greaterThan=valueName,valueToTest", array('result'));
				$this->core->registerFeature($this, array('between'), 'between', "Restrict the resultset to items where a named result value is between two specified values. --between=valueName,smallValue,largeValue", array('result'));
				
				$this->core->registerFeature($this, array('sortOnKey'), 'sortOnKey', "Sort items by key.", array('result', 'sort'));
				
				#$this->core->registerFeature($this, array('cleanUnresolvedStoreVars'), 'cleanUnresolvedStoreVars', 'Clean out any store variables that have not been resolved. This is important when a default should be blank.', array('array', 'escaping', 'result'));
				
				$this->core->registerFeature($this, array('createOneResult'), 'createOneResult', 'Replaces the resultSet with a single entry that can then be manipulated using features like --resultSet.', array('array', 'result'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'requireEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'recursiveRequireEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event), false, true, true);
				break;
			case 'requireItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'excludeEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event), false, false);
				break;
			case 'recursiveExcludeEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event), false, false, true);
				break;
			case 'excludeItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1], false, false);
				break;
			case 'manipulateEach':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 2);
				return $this->requireEach($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'manipulateItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 3);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);
				break;
			case 'toString':
				return $this->toString($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'flatten':
				$limitIn=$this->core->get('Global', $event);
				if ($limitIn == null) $limit=-1;
				elseif ($limitIn==0) $limit=false;
				else $limit=$limitIn;
				return $this->flatten($this->core->getResultSet(), $limit);
				break;
			case 'finalFlatten':
				return $this->finalFlatten($this->core->getResultSet());
				break;
			case 'replace':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				return $this->replaceUsingRegex($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'unique':
				return $this->unique($this->core->getResultSet());
				break;
			case 'chooseFirst':
				return $this->chooseFirst($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)));
				break;
			case 'chooseFirstSet':
				return $this->chooseFirstSet($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)));
				break;
			case 'chooseFirstSetIfNotSet':
				return $this->chooseFirstSet($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)), false);
				break;
			case 'resultSet':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->resultSet($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'resultSetIfNotSet':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->resultSet($this->core->getResultSet(), $parms[0], $parms[1], false);
				break;
			case 'resultUnset':
				return $this->resultUnset($this->core->getResultSet(), explode(',', $this->core->get('Global', $event)));
				break;
			case 'cleanUnresolvedResultVars':
				return $this->cleanUnresolvedVars($this->core->getResultSet(), resultVarBegin, resultVarEnd);
				break;
			case 'take':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				return $this->take($parms[0], $this->core->getResultSet());
				break;
			case 'takeSubResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				return $this->takeSubResult($parms[0], $this->core->getResultSet());;
				break;
			case 'addSlashes':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->addResultSlashes($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'duplicate':
				return $this->duplicate($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'count':
				return array(count($this->core->getResultSet()));
				break;
			case 'countToVar':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2);
				$this->core->set($parms[0], $parms[1], count($this->core->getResultSet()));
				break;
			case 'pos':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2);
				return $this->assignPos($this->core->getResultSet(), $parms[0], $parms[1]);;
				break;
			case 'chooseBasedOn':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 3, true);
				return $this->chooseBasedOn($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);;
				break;
			case 'crc':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2, true);
				return $this->crc($this->core->getResultSet(), $parms[0], $parms[1], false);;
				break;
			case 'positiveCRC':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2, true);
				return $this->crc($this->core->getResultSet(), $parms[0], $parms[1], true);;
				break;
			case 'firstResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 0);
				return $this->offsetResult($this->core->getResultSet(), 0, $parms[0]);
				break;
			case 'lastResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 0);
				$number=($parms[0])?$parms[0]*-1:-1;
				return $this->offsetResult($this->core->getResultSet(), 0, $number);
				break;
			case 'offsetResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->offsetResult($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'createOneResult':
				return array(array());
				break;
			case 'keyOn':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 1);
				return $this->keyOn($this->core->getResultSet(), $parms[0]);
				break;
			case 'keyValueOn':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->keyValueOn($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'sortOnKey':
				return ksort($this->core->getResultSet());
				break;
			case 'lessThan':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->lessThan($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'greaterThan':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->greaterThan($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'between':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3);
				return $this->between($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function replace($input, $search, $replace)
	{
		$output=implode($replace, explode($search, $input));;
		$this->core->debug(4, "replace: Search=$search Replace=$replace Input=\"$input\" Output=\"$output\"");
		return $output;
	}
	
	function toString($input, $template)
	{
		$output=array();
		
		foreach ($input as $line)
		{
			if (is_array($line))
			{
				# TODO It would be nice to make this recursive.
				$outputLine=$this->core->processValue($template);
				#foreach ($line as $key=>$value)
				#{
					//$this->core->debug(0, "$key, $value, $outputLine");
					$outputLine=$this->processResultVarsInString($line, $outputLine);
				#}
				$output[]=$outputLine;
			}
			else
			{
				$output[]=$this->replace($this->core->processValue($template), resultVarBegin.'value'.resultVarEnd, $line);
			}
		}
		
		return $output;
	}
	
	function processResultVarsInString($input, $string)
	{
		# TODO This really needs to recursively go through the result set since it can be nested.
		$outputLine=$string;;
		
		foreach ($input as $key=>$value)
		{
			if (!is_array($value)) $outputLine=$this->replace($outputLine, resultVarBegin."$key".resultVarEnd, $value);
			else $this->core->debug(4, "processResultVarsInString: value for key $key is an array, so the replace has not been attempted.");
			# $this->core->debug(3, "processResultVarsInString: In=\"$string\" Out=\"$outputLine\" Search=$key Value=$value");
		}
		
		return $outputLine;
	}
	
	function cleanUnresolvedVars($input, $begin, $end)
	{
		if (is_array($input))
		{
			$output=array();
			foreach ($input as $key=>$value) $output[$key]=$this->cleanUnresolvedVars($value, $begin, $end);
			return $output;
		}
		else
		{
			return $this->cleanUnresolvedVarsFromString($input, $begin, $end);
		}
		
		
	}
	
	function cleanUnresolvedVarsFromString($input, $begin, $end)
	{
		$start=strpos($input, $begin);
		if (!$start) return $input;
		$finish=strpos($input, $end)+strlen($end);
		$termite=substr($input, $start, $finish-$start);
		$output=$this->replace($input, $termite, '');
		
		if (strpos($output, $begin)!==false) return $this->cleanUnresolvedVarsFromString($output, $begin, $end);
		else return $output;
	}
	
	function flatten($input, $limit, $nesting=0)
	{
		if (!is_array($input)) return $input;
		
		$output=array();
		$clashes=array();
		if (is_numeric($limit) and $limit<0)
		{
			foreach ($input as $key=>$line)
			{
				$newLimit=($limit<-1)?$limit+1:false;
				$output[$key]=$this->flatten($line, $newLimit, $nesting+1);
			}
		}
		else $this->getArrayNodes($output, $input, $clashes, $limit, $nesting);
		
		return $output;
	}
	
	function finalFlatten($dataIn)
	{
		$output=array();
		
		foreach ($dataIn as $line)
		{
			if (is_array($line))
			{
				foreach ($line as $subline)
				{
					$output[]=$subline;
				}
			}
			else $output[]=$line;
		}
		
		return $output;
	}
	
	function replaceUsingRegex($dataIn, $search, $replace)
	{
		$searchArray=array("/$search/");
		$replaceArray=array($replace);
		$output=array();
		
		foreach ($dataIn as $line)
		{
			$output[]=preg_replace($searchArray, $replaceArray, $line);
		}
		
		return $output;
	}
	
	function unique($dataIn)
	{
		$output=array();
		
		foreach ($dataIn as $line)
		{
			if (is_string($line))
			{
				$output[md5($line)]=$line;
			}
			else $output[]=$line;
		}
		
		return $output;
	}
	
	private function getArrayNodes(&$output, $input, &$clashes, $limit, $nesting)
	{
		foreach ($input as $key=>$value)
		{
			if (is_array($value) and !(is_numeric($limit) and (($nesting>=$limit))))
			{
				$this->getArrayNodes($output, $value, $clashes, $limit, $nesting+1);
			}
			else
			{
				if (is_numeric($key)) $output[]=$value;
				else
				{
					if (!isset($output[$key])) $output[$key]=$value;
					else
					{
						# work out new key based on clashes
						$clashes[$key]=(isset($clashes[$key]))?$clashes[$key]+1:1;
						$newKey="$key{$clashes[$key]}";
						$output[$newKey]=$value;
					}
					
				}
			}
		}
	}
	
	private function mixResults($matching, $notMatching, $feature)
	{
		$featureParts=$this->core->splitOnceOn(' ', $feature);
		$processed=$this->core->callFeatureWithDataset($featureParts[0], $featureParts[1], $matching);
		
		return array_merge($processed, $notMatching);
	}
	
	private function requireEach($input, $search, $feature=false, $shouldMatch=true, $shouldRecurse=false)
	{
		//print_r($input);
		$outputMatch=array();
		$outputNoMatch=array();
		
		# This could techinically be done with return array();, but would be prone to bugs if the default value of one of these arrays changes in the future.
		if (!is_array($input)) return ($shouldMatch)?$outputMatch:$outputNoMatch;
		
		foreach ($input as $key=>$line)
		{
			$processed=false;
			
			if (is_string($line))
			{
				if (preg_match('/'.$search.'/', $line))
				{
					$this->core->debug(2, "requireEach: Matched \"$search\" in \"$line\"");
					$outputMatch[$key]=$line;
				}
				else $outputNoMatch[$key]=$line;
			}
			elseif (is_array($line))
			{
				$matched=false;
				foreach ($line as $subline)
				{
					$matched=false;
					if (is_string($subline))
					{
						if (preg_match('/'.$search.'/', $subline))
						{
							$outputMatch[$key]=$line;
							$matched=true;
							break;
						}
					}
					elseif ($shouldRecurse and is_array($subline))
					{
						$subResult=$this->requireEach($subline, $search, $feature, true, true);
						#echo "$search\n";
						#print_r($subline);
						#print_r($subResult);
						#die();
						if (count($subResult))
						{
							$outputMatch[$key]=$line;
							$matched=true;
							break;
						}
					}
				}
				if (!$matched) $outputNoMatch[$key]=$line;
			}
			else $outputNoMatch[$key]=$line;
		}
		
		if ($feature)
		{
			$this->core->debug(3, 'requireEach: Matched '.count($outputMatch).". Didn't match ".count($outputNoMatch.". For search $search")); # TODO Optimise this so that the counts are not done if the debugging isn't going to be seen
			return $this->mixResults($outputMatch, $outputNoMatch, $feature);
		}
		else
		{
			if ($shouldMatch) return $outputMatch;
			else return $outputNoMatch;
		}
	}
	
	private function requireEntry($input, $neededKey, $neededRegex, $feature=false, $shouldMatch=true)
	{
		$outputMatch=array();
		$outputNoMatch=array();
		
		if (!is_array($input)) return false; # TODO double check what this should be.
		
		foreach ($input as $key=>$line)
		{
			if ($neededKey)
			{
				if (isset($line[$neededKey]))
				{
					if ($neededRegex)
					{
						if (preg_match('/'.$neededRegex.'/', $line[$neededKey])) $outputMatch[$key]=$line;
						else $outputNoMatch[$key]=$line;
					}
					else $outputMatch[$key]=$line;
				}
				else $outputNoMatch[$key]=$line;
			}
			else
			{
				if (is_array($line))
				{
					if (count($this->requireEach($line, $neededRegex))) $outputMatch[$key]=$line;
					else $outputNoMatch[$key]=$line;
				}
				else $outputNoMatch[$key]=$line;
			}
		}
		
		if ($feature)
		{
			$this->core->debug(3, 'requireEntry: Matched '.count($outputMatch).". Didn't match ".count($outputNoMatch).". For search $neededKey=$neededRegex"); # TODO Optimise this so that the counts are not done if the debugging isn't going to be seen
			return $this->mixResults($outputMatch, $outputNoMatch, $feature);
		}
		else
		{
			if ($shouldMatch) return $outputMatch;
			else return $outputNoMatch;
		}
	}
	
	function chooseFirst($input, $parms)
	{
		# Choose the first non-empty value and put it into the destination variable. --chooseFirst=dstVarName,srcVarName1,srcVarName2[,srcVarName3[,...]]
		
		$dstVarName=$parms[0];
		$totalParms=count($parms);
		$output=array();
		
		foreach ($input as $line)
		{
			//$line[$dstVarName]='unset'; # Do we want this?
			for ($i=1;$i<$totalParms;$i++)
			{
				$value=(isset($line[$parms[$i]]))?$line[$parms[$i]]:'';
				if ($value)
				{
					$line[$dstVarName]=$value;
					break;
				}
			}
			
			$output[]=$line;
		}
		
		return $output;
	}
	
	function chooseFirstSet($dataIn, $parms, $overwrite=true)
	{
		# TODO write this
		/*
			build an array of sets
			test each set for each inputItem
				assign results
			return result
		*/
		
		$stop=count($parms);
		$width=$parms[0];
		$sets=array(0=>array());
		$setID=-1;
		$output=array();
		$destination=0;
		
		
		for ($inputKey=1;$inputKey<$stop;$inputKey++)
		{
			if ($inputKey%$width==1)
			{
				$setID++;
				$sets[$setID]=array();
			}
			
			$sets[$setID][]=$parms[$inputKey];
		}
		
		foreach ($dataIn as $line)
		{
			if ($overwrite or !isset($line[$sets[0][0]]))
			{
				for ($setToTest=1;$setToTest<=$setID;$setToTest++)
				{
					$value=(isset($line[$sets[$setToTest][0]]))?$line[$sets[$setToTest][0]]:'';
					if ($value)
					{
						foreach ($sets[0] as $key=>$destinationField)
						{
							$valueToCopy=(isset($line[$sets[$setToTest][$key]]))?$line[$sets[$setToTest][$key]]:'';
							$line[$sets[0][$key]]=$line[$sets[$setToTest][$key]];
						}
						break;
					}
				}
			}
			$output[]=$line;
		}
		
		return $output;
	}
	
	function resultSet($input, $key, $value, $overwrite=true)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			if ($overwrite or !isset($line[$key])) $line[$key]=$this->processResultVarsInString($line, $value);
		}
		
		return $output;
	}
	
	function resultUnset($input, $keys)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			foreach ($keys as $key)
			{
				if (isset($line[$key])) unset($line[$key]);
			}
		}
		
		return $output;
	}
	
	function addResultSlashes($input, $src, $dst)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			$line[$dst]=addslashes($line[$src]);
		}
		
		return $output;
	}
	
	function take($key, $resultSet)
	{
		$output=array();
		
		foreach ($resultSet as $line)
		{
			if (isset($line[$key]))
			{
				if (false) //(is_array($line[$key]))
				{ # TODO I don't think this is correct!
					foreach ($line[$key] as $subline)
					{
						$output[]=$subline;
					}
				}
				else $output[]=$line[$key];
			}
		}
		
		return $output;
	}
	
	function takeSubResult($key, $resultSet)
	{
		if (isset($resultSet[$key])) return $resultSet[$key];
		else return array(); # Failure should empty the resultSet
	}
	
	function duplicate($input, $numberOfTimesToDuplicate=1)
	{
		$output=array();
		
		$actualNumberOfTimesToDuplicate=($numberOfTimesToDuplicate)?$numberOfTimesToDuplicate:1;
		
		for($duplication=0;$duplication<=$actualNumberOfTimesToDuplicate;$duplication++)
		{
			foreach ($input as $line)
			{
				$output[]=$line;
			}
		}
		
		return $output;
	}
	
	function assignPos($resultSet, $resultVariableName='pos', $offset=0)
	{
		if (!$resultVariableName) $resultVariableName='pos';
		if (!$offset) $offset=0;
		
		$pos=$offset;
		foreach ($resultSet as $key=>&$result)
		{
			$result[$resultVariableName]=$pos;
			$pos++;
		}
		
		return $resultSet;
	}
	
	function chooseBasedOn($resultSet, $inputValueName, $outputValueName, $inputArrayName)
	{
		if ($inputArray=$this->core->getNested($this->core->interpretParms($inputArrayName)))
		{
			if (is_array($inputArray))
			{
				$index=array_keys($inputArray);
				$count=count($inputArray);
				
				foreach ($resultSet as $key=>&$item)
				{
					if (!isset($item[$inputValueName])) continue; # TODO Do we need debugging on this? Probably yes.
					if (!is_numeric($item[$inputValueName])) continue; # TODO Do we need debugging on this? Probably yes.
					
					# TODO test/finish this
					$arrayPos=$item[$inputValueName]%$count;
					#$item[$outputValueName]=$inputArray[$index[$arrayPos]];
					$item[$outputValueName]=$index[$arrayPos];
				}
				
				
				
				return $resultSet;
			}
			else
			{
				$this->core->debug(3, "Manipulator->chooseBasedOn: $inputArrayName was not an array. It's ".gettype($inputArray));
				return false;
			}
		}
		else 
		{
			$this->core->debug(3, "Manipulator->chooseBasedOn: $inputArrayName did not exist.");
			return false;
		}
	}
	
	function crc($resultSet, $inputValueName, $outputValueName)
	{
		foreach ($resultSet as $key=>&$item)
		{
			if (isset($item[$inputValueName]))
			{
				$item[$outputValueName]=abs(crc32($item[$inputValueName]));
			}
			else $this->core->debug(3, __CLASS__."->crc(<dataset>, $inputValueName, $outputValueName): Inputvalue $inputValueName did not exist in row with key $key.");
		}
		
		return $resultSet;
	}
	
	function offsetResult($resultSet, $offset, $max)
	{
		if (!$max) $max=1;
		
		$output=array();
		$keys=array_keys($resultSet);
		$keyCount=count($keys);
		$absMax=abs($max);
		$totalRequested=$offset+$absMax;
		
		if ($totalRequested > $keyCount) $stop=$keyCount;
		else $stop=$totalRequested;
		
		if ($max<0)
		{
			$oldOffset=$offset;
			$offset=$keyCount-$stop;
			$stop=$keyCount-$oldOffset;
		}
		
		for ($i=$offset; $i<$stop; $i++)
		{
			$key=$keys[$i];
			$output[$key]=$resultSet[$key];
		}
		
		return $output;
	}
	
	function keyOn($resultSet, $valueName)
	{
		$output=array();
		
		foreach ($resultSet as $oldKey=>$item)
		{
			$key=(isset($item[$valueName]))?$item[$valueName]:$oldKey;
			$output[$key]=$item;
		}
		
		return $output;
	}
	
	function keyValueOn($resultSet, $valueName, $subValueName)
	{
		foreach ($resultSet as $oldKey=>&$item)
		{
			if (isset($item[$valueName]))
			{
				$item[$valueName]=$this->keyOn($item[$valueName], $subValueName);
			}
		}
		
		return $resultSet;
	}
	
	function findPoint($resultSet, $method, $valueName, $value)
	{ // Divide and conquer to find an approximate value.
		
		$keys=array_keys($resultSet);
		$min=0;
		$total=count($keys);
		$max=$total-1;
		$interations=0;
		$half=intval(($max-$min)/2);
		
		while ($interations<$total)
		{
			$iterationValue=$resultSet[$keys[$half]][$valueName];
			$maxValue=$resultSet[$keys[$max]][$valueName];
			$minValue=$resultSet[$keys[$min]][$valueName];
			$this->core->debug(3, "findPoint: Iteration $interations min=$min half=$half max=$max");
			
			if ($iterationValue == $value and $method == '==') return $half;
			elseif ($max==$min or $max==$half) # TODO potentially we don't need $max==$min
			{
				switch ($method) # TODO is this really right? It seems simpler than I imagined...
				{
					case '==':
						return $half;
					case '>':
						return $half;
					case '<':
						return $half;
				}
			}
			elseif ($iterationValue>$value)
			{
				$this->core->debug(3, "findPoint: ($iterationValue>$value) Set max to $half");
				$max=$half;
			}
			else
			{
				$this->core->debug(3, "findPoint: (else) Set min to $half");
				$min=$half;
			}
			
			$difference=$max-$min;
			if ($difference>1) $half=intval($difference/2)+$min;
			else $half=$max;
			$interations++;
		}
		$this->core->debug(2, "findPoint: Finished having done $interations iterations.");
		return $half;
	}
	
	function getRange($resultSet, $start, $stop)
	{
		if (!$stop) $stop=count($resultSet)-1;
		$keys=array_keys($resultSet);
		$output=array();
		$this->core->debug(3, "getRange(---, $start, $stop)");
		
		for ($i=$start;$i<=$stop;$i++)
		{
			$output[$keys[$i]]=$resultSet[$keys[$i]];
		}
		
		return $output;
	}
	
	function lessThan($resultSet, $valueName, $value)
	{
		$this->core->debug(3, "lessThan(---, $valueName, $value)");
		$point=$this->findPoint($resultSet, '<', $valueName, $value);
		$range=$this->getRange($resultSet, 0, $point);
		return $range;
	}
	
	function greaterThan($resultSet, $valueName, $value)
	{
		$this->core->debug(3, "greaterThan(---, $valueName, $value)");
		$point=$this->findPoint($resultSet, '>', $valueName, $value);
		$range=$this->getRange($resultSet, $point, false);
		return $range;
	}
	
	function between($resultSet, $valueName, $smallValue, $largeValue)
	{
		$this->core->debug(3, "between(---, $valueName, $smallValue, $largeValue)");
		$startPoint=$this->findPoint($resultSet, '>', $valueName, $smallValue);
		$stopPoint=$this->findPoint($resultSet, '<', $valueName, $largeValue);
		$range=$this->getRange($resultSet, $startPoint, $stopPoint);
		return $range;
	}
}

$core=core::assert();
$core->registerModule(new Manipulator());
 
?>