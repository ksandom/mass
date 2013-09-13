<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

define('valueSeparator', ',');
define('storeValueBegin', '~!');
define('storeValueEnd', '!~');

define('resultVarBegin', '~%');
define('resultVarEnd', '%~');

define('resultVarsDefaultMaxRecusion', 50); // Prevent a stack overflow. We could go many times deeper than this, but if we get this far, sompething is very likely wrong.
define('resultVarsDefaultRecusionWarn', 25); // If we get to this many (arbitrary) levels of recusion, something is probably wrong
define('resultVarsDefaultWarnDebugLevel', 2);
define('resultVarsDefaultSevereDebugLevel', 1);

define('nestedPrivateVarsName', 'Me');
define('isolatedNestedPrivateVarsName', 'Isolated');

define('workAroundIfBug', true); // See doc/bugs/ifBug.md


/*
	Debug levels
		0 Default - Don't use this normally
		1 Important
		2 Warning
		3 Good to know
		4 
		5 Mother in law
*/

class core extends Module
{
	private $store;
	private $module;
	private static $singleton;
	private $verbosity=0;
	private $initMap=array();
	
	function __construct($verbosity=0)
	{
		$this->store=array();
		$this->module=array();
		
		$this->verbosity=$verbosity;
		$this->set('Verbosity', 'level', $verbosity);
		
		# This is for setting default default result set so that functions definitely get the data type they are expecting.
		#$defaultResultSet=array();
		#$this->setResultSet($defaultResultSet, 'start up');
		
		parent::__construct('Core');
		$this->set('Core', 'serial', intval(rand()));
		$this->registerModule($this);
	}
	
	public function dumpState()
	{
		return array('Store'=>$this->store, 'Module'=>$this->module);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->registerFeature($this, array('registerTags'), 'registerTags', 'Register tags to a feature. --registerTags=featureName'.valueSeparator.'tag1['.valueSeparator.'tag2['.valueSeparator.'tag3'.valueSeparator.'...]]');
				$this->registerFeature($this, array('aliasFeature'), 'aliasFeature', 'Create an alias for a feature. Eg aliasing --help to -h and -h1 would be done by --aliasFeature=help'.valueSeparator.'h'.valueSeparator.'h1');
				$this->registerFeature($this, array('setFeatureAttribute'), 'setFeatureAttribute', 'Set a feature attribute. --setFeatureAttribute=featureName,attributeName,attributeValue');
				# $this->registerFeature($this, array('get'), 'get', 'Get a value. --get=category'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('getToResult', 'get'), 'getToResult', 'Get a value and put it in an array so we can do stuff with it. --getToResult=category'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('set'), 'set', 'set a value. All remaining values after the destination go into a string. --set=category'.valueSeparator.'variableName'.valueSeparator.'value', array('storeVars'));
				$this->registerFeature($this, array('setNested'), 'setNested', 'set a value into a nested array, creating all the necessary sub arrays. The last parameter is the value. All the other parameters are the keys for each level. --setNested=StoreName'.valueSeparator.'category'.valueSeparator.'subcategory'.valueSeparator.'subsubcategory'.valueSeparator.'value. In this case an array would be created in StoreName,category that looks like this subcategory=>array(subsubcategory=>value)', array('storeVars'));
				$this->registerFeature($this, array('setArray'), 'setArray', 'set a value. All remaining values after the destination go into an array. --set=category'.valueSeparator.'variableName'.valueSeparator.'value', array('storeVars'));
				$this->registerFeature($this, array('setIfNotSet', 'setDefault'), 'setIfNotSet', 'set a value if none has been set. --setIfNotSet=category'.valueSeparator.'variableName'.valueSeparator.'defaultValue', array('storeVars'));
				$this->registerFeature($this, array('setIfNothing'), 'setIfNothing', 'set a value if none has been set or evaluates(PHP) to false. --setIfNothing=category'.valueSeparator.'variableName'.valueSeparator.'defaultValue', array('storeVars'));
				$this->registerFeature($this, array('unset'), 'unset', 'un set (delete) a value. --unset=category'.valueSeparator.'variableName['.valueSeparator.'variableName['.valueSeparator.'variableName]] . Note that the extra optional variableNames are for nesting, not for deleting multiple variables in one command.', array('storeVars'));
				$this->registerFeature($this, array('getCategory'), 'getCategory', 'Get an entire store into the result set. --getCategory=moduleNam', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('setCategory'), 'setCategory', 'Set an entire store to the current state of the result set. --setCategory=category', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('unsetCategory'), 'unsetCategory', 'Un set/delete an entire store. --unsetCategory=category', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('stashResults'), 'stashResults', 'Put the current result set into a memory slot. --stashResults=category'.valueSeparator.'variableName');
				$this->registerFeature($this, array('retrieveResults'), 'retrieveResults', 'Retrieve a result set that has been stored. This will replace the current result set with the retrieved one --retrieveResults=category'.valueSeparator.'variableName');
				$this->registerFeature($this, array('getPID'), 'getPID', 'Save the process ID to a variable. --getPID=category'.valueSeparator.'variableName');
				
				$this->registerFeature($this, array('setJson'), 'setJson', 'Take a json encoded array from jsonValue and store the arrary in category'.valueSeparator.'variableName. --setJson=category'.valueSeparator.'variableName'.valueSeparator.'jsonValue');
				$this->registerFeature($this, array('outNow'), 'outNow', 'Execute the output now.', array('dev'));
				
				$this->registerFeature($this, array('callFeature', 'callFeatureReturn'), 'callFeatureReturn', "Call a feature. This essentially allows you to execute what ever is in a variable. Take a lot of care to make sure your variables contain what you think they do as you could introduce a lot of pain here. --callFeature=feature[,parm1[,parm2..etc]]", array('dangerous'));
				$this->registerFeature($this, array('callFeatureNoReturn', 'isolate'), 'callFeatureNoReturn', "Call a feature but don't return the results. There's two main uses for this. 1) Call something that would normally interfere with the result set. 2) Execute what ever is in a variable without affecting what is in the resultSet. Take a lot of care to make sure your variables contain what you think they do as you could introduce a lot of pain here. This can also be used to isolate a feature so that it doesn't affect the following feature calls. --callFeatureNoReturn=feature[,parm1[,parm2..etc]]", array('dangerous'));
				
				$this->registerFeature($this, array('dump'), 'dump', 'Dump internal state.', array('debug', 'dev'));
				$this->registerFeature($this, array('debug'), 'debug', 'Send parameters to stdout. --debug=debugLevel,outputText eg --debug=0,StuffToWriteOut . DebugLevel is not implemented yet, but 0 will be "always", and above that will only show as the verbosity level is incremented with -v or --verbose.', array('debug', 'dev'));
				$this->registerFeature($this, array('verbose', 'v', 'verbosity'), 'verbose', 'Increment/set the verbosity. --verbose[=verbosityLevel] where verbosityLevel is an integer starting from 0 (default)', array('debug', 'dev'));
				$this->registerFeature($this, array('V'), 'V', 'Decrement verbosity.', array('debug', 'dev'));
				$this->registerFeature($this, array('ping'), 'ping', 'Useful for debugging.', array('debug', 'dev'));
				$this->registerFeature($this, array('cleanResults'), 'cleanResults', 'Cleans keys. Converts any objects to arrays.', array('resultSet'));
				$this->registerFeature($this, array('#'), '#', 'Comment.', array('systemInternal'));
				$this->registerFeature($this, array('pass'), 'pass', "It's a place holder meaning that you will not get a message like \"Could not find macro 'default'. This can happen if you haven't asked me to do anything.\"", array('systemInternal'));
				$this->registerFeature($this, array('	'), '	', 'Internally used for nesting.', array('systemInternal'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			#case 'get': # TODO Is this still useful?
			#	$parms=$this->interpretParms($this->get('Global', 'get'));
			#	return $this->get($parms[0], $parms[1]);
			#	break;
			
			# TODO Oops! Somehow I missed this. It should use already existing functionality.
			#case 'registerTags':
			#	break;
			case 'aliasFeature':
				$parms=$this->interpretParms($this->get('Global', 'aliasFeature'));
				$this->aliasFeature($parms[0], $parms);
				break;
			case 'setFeatureAttribute':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 3, true);
				$this->setFeatureAttribute($parms[0], $parms[1], $parms[2]);
				break;
			case 'getToResult':
				$parms=$this->interpretParms($this->get('Global', 'getToResult'));
				return array($this->get($parms[0], $parms[1]));
				break;
			case 'set':
				$parms=$this->interpretParms($this->get('Global', 'set'), 2, 2, true);
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'setNested':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 3, false);
				$this->setNested($parms[0], $parms[1], $parms[2]);
				break;
			case 'setArray':
				$parms=$this->interpretParms($this->get('Global', 'setArray'), 2, 2, false);
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'setIfNotSet':
				$originalParms=$this->get('Global', $event);
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 3, $event, $originalParms, $parms);
				$this->setIfNotSet($parms[0], $parms[1], $parms[2]);
				break;
			case 'setIfNothing':
				$originalParms=$this->get('Global', $event);
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 3, $event, $originalParms, $parms);
				$this->setIfNotSet($parms[0], $parms[1], $parms[2], true);
				break;
			case 'unset':
				$parms=$this->interpretParms($this->get('Global', $event), 0, 2, false);
				$this->doUnset($parms);
				break;
			case 'getCategory':
				return $this->getCategoryModule($this->get('Global', 'getCategory'));
				break;
			case 'setCategory':
				$this->setCategoryModule($this->get('Global', 'setCategory'), $this->getResultSet());
				break;
			case 'unsetCategory':
				$this->unsetCategoryModule($this->get('Global', 'unsetCategory'));
				break;
			case 'stashResults':
				$originalParms=$this->get('Global', 'stashResults');
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 2, $event, $originalParms, $parms);
				$this->set($parms[0], $parms[1], $this->core->getResultSet());
				break;
			case 'retrieveResults':
				$originalParms=$this->get('Global', 'retrieveResults');
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->get($parms[0], $parms[1]);
				break;
			case 'setJson':
				$parms=$this->interpretParms($this->get('Global', 'setJson'));
				echo $this->get('Global', 'setJson')."\n";
				$this->set($parms[0], $parms[1], json_decode($parms[2]));
				break;
			case 'dump':
				return $this->dumpState();
				break;
			case 'callFeatureReturn':
				$parms=$this->splitOnceOn(valueSeparator, $this->get('Global', $event));
				return $this->callFeature($parms[0], $parms[1]);
				break;
			case 'callFeatureNoReturn':
				$parms=$this->splitOnceOn(valueSeparator, $this->get('Global', $event));
				$this->callFeature($parms[0], $parms[1]);
				break;
			case 'debug':
				$parms=$this->interpretParms($this->get('Global', 'debug'), 1, 1, true);
				$this->debug($parms[0], $parms[1]);
				break;
			case 'verbose':
				$original=$this->get('Global', 'verbose');
				$this->verbosity($original);
				break;
			case 'V':
				$this->verbosity('-');
				break;
			case 'getPID':
				$this->getPID($this->interpretParms($this->get('Global', 'getPID')));
				break;
			case 'ping':
				echo "Pong.\n";
				break;
			case 'cleanResults':
				return $this->objectToArray($this->getResultSet());
				break;
			case 'outNow':
				$this->out($this->getResultSet());
				break;
			case 'pass':
				break;
			case '#':
				break;
			default:
				$this->complain($this, 'Unknown event', $event);
				break;
		}
	}

	public static function assert($verbosity=0)
	{
		if (!self::$singleton) self::$singleton=new core($verbosity);
		return self::$singleton;
	}
	
	function interpretParms($parms, $limit=0, $require=null, $reassemble=true)
	{
		if (strlen($parms)<1)
		{ // No parms$require
			# TODO Potentially some detection could happen here to allow atomic failure.
			if ($require>0) $this->debug(0, "Expected $require parameters, but got nothing. Bad things could happen if execution had been allowed to continue. Parms=$parms");
			$parts=array();
		}
		else
		{
			
			
			$firstChar=substr($parms, 0, 1);
			if ($firstChar=='[' or $firstChar=='{')
			{ // Json
				$parts=json_decode($parms, 1);
				if (!count($parts))
				{
					$this->debug(0, "interpretParms: Got 0 parts back from json \"$parms\" which usually means invalid json. I've caused this myself when chosing the wrong combination of { vs [.");
					
					# TODO Do we want to do some other clean up here.
					$parts=array(); // Prevent stuff from breaking since they always expect an array.
				}
			}
			else
			{ // Legacy comma separated format
				$parts=explode(valueSeparator, $parms);
			}
		}
		
		
		#if ($require===null) $require=$limit;
		
		$partsCount=count($parts);
		
		if ($partsCount<$limit)
		{
			if ($partsCount<$require) $this->debug(0, "Expected $require parameters, but got $partsCount. Bad things could happen if execution had been allowed to continue. Parms=$parms");
			
			$output=$parts;
			while (count($output)< $limit) $output[]='';
			return $output;
		}
		
		for ($i=$partsCount;$i<$limit;$i++) $parts[$i]=false;
		
		if ($limit)
		{
			# Return the split array, but once we reach the limit, dump any remaining parms into one remaining parm
			$output=array();
			for ($i=0;$i<$limit;$i++)
			{
				if (isset($parts[$i]))
				{
					# $this->core->debug(2, "interpretParms: Added main part $i => {$parts[$i]}");
					$output[]=$parts[$i];
				}
				else break;
			}
			
			$outputParts=array();
			$stop=count($parts);
			
			for ($j=$i;$j<$stop;$j++)
			{
				$outputParts[]=$parts[$j];
				# $this->core->debug(2, "interpretParms: Added remaining part $j => {$parts[$j]}");
			}
			
			# Reassemble=true sets a string. False sets an array.
			if ($reassemble) $output[]=implode(valueSeparator, $outputParts);
			else $output[]=$outputParts;
			
			while (count($output)< $limit)
			{
				$this->debug(0, "add one");
				$output[]='';
			}
			
			return $output;
		}
		else return $parts;
	}
	
	function getFileList($path)
	{
		# TODO This can be done much better internally in PHP
		if (is_file($path))
		{
			$pathParts=explode('/', $path);
			$fileName=$pathParts[count($pathParts)-1];
			$output=array($fileName=>$path);
		}
		else
		{
			$output=array();
			$files=explode("\n", `ls -1 $path`);
			foreach ($files as $file)
			{
				$trimmedFile=trim($file);
				if ($trimmedFile) $output[$trimmedFile]="$path/$trimmedFile";
			}
		}
		return $output;
	}
	
	function getModules($path)
	{ // get all the module paths from a path
		return $this->getFileList($path);
	}
	
	function setResultSet(&$value, $src='unknown')
	{
		$valueText=(is_string($value))?$value:'Type='.gettype($value);
		$this->debug(5, "setResultSet(value=$valueText, src=$src)");
		if (is_array($value)) # ($value!=null and $value!==false)
		{
			$nesting=$this->get('Core', 'nesting');
			if ($this->isVerboseEnough(5))
			{
				$numberOfEntries=count($value);
				$this->debug(5, "setResultSet(value=$value($numberOfEntries), src=$src)/$nesting - is_array == true. VALUE WILL BE SET");
				if ($this->isVerboseEnough(6)) 
				{
					print_r($value);
					$this->debug(6, "setResultSet(value=$value($numberOfEntries), src=$src)/$nesting - exiting from var dump.");
				}
				$serial=$this->get('Core', 'serial');
				$this->debugResultSet("setResultSet $src/$serial");
			}
			$this->setRef('Core', 'shared'.$nesting, $value);
			return true;
		}
		else return false;
	}
	
	function &getResultSet()
	{
		$nesting=$this->get('Core', 'nesting');
		$resultSetDiag=count($this->get('Core', 'shared'.$nesting));
		if ($this->isVerboseEnough(5))
		{
			$serial=$this->get('Core', 'serial');
			$this->debug(5, "getResultSet/$nesting count=$resultSetDiag serial=$serial");
			#print_r($this->get('Core', 'shared'.$nesting));
		}
		return $this->get('Core', 'shared'.$nesting);
	}
	
	function &getParentResultSet()
	{
		$nesting=$this->get('Core', 'nesting');
		$nestingSrc=$nesting-1;
		if ($nestingSrc<1 or !is_numeric($nestingSrc)) $nestingSrc = 1; # TODO check this
		$resultSet=&$this->get('Core', 'shared'.$nestingSrc);
		
		if ($this->isVerboseEnough(5))
		{
			$serial=$this->get('Core', 'serial');
			$resultSetDiag=count($resultSet);
			$this->debug(5, "getParentResultSet $nestingSrc->$nesting/$resultSetDiag/$serial");
		}
		return $resultSet;
	}
	
	function makeParentShareMemoryCurrent()
	{
		$this->debug(5, "makeParentShareMemoryCurrent/");
		$this->setResultSet($this->getParentResultSet());
	}
	
	function callFeatureWithDataset($argument, $value, $dataset)
	{
		// Increment nesting
		$this->incrementNesting();
		
		// set resultSet to dataset
		$this->setResultSet($dataset);
		
		// call feature
		$output=$this->callFeature($argument, $value);
		
		// Decrement nesting (WITHOUT pulling the resultSet)
		$this->decrementNesting();
		return $output;
	}
	
	function callFeature($argument, $value='')
	{
		$nesting=$this->get('Core', 'nesting');
		
		if ($argument and $argument != '#' and $argument != '//')
		{ // Only process non-white space
			$obj=&$this->core->get('Features', $argument);
			if (is_array($obj))
			{
				$indentation=str_repeat('  ', $nesting);
				$valueIn=$this->processValue($value);
				
				$this->debug(4, "INVOKE-Enter {$indentation}{$obj['name']}/$nesting value={$value}, valueIn=$valueIn");
				
				if ($this->isVerboseEnough(5))
				{
					$this->debugResultSet($obj['name']);
				}
				
				$this->makeArgsAvailableToTheScript($obj['name'], $valueIn);
				$result=$obj['obj']->event($obj['name']);
				
				if (isset($obj['featureType']))
				{
					$this->core->debug(4, "callFeature: ".$obj['featureType']);
					if ($outDataType=$this->getNested(array('Semantics', 'featureTypes', $obj['featureType'], 'outDataType')))
					{
						if ($dataType=$this->getNested(array('Semantics', 'dataTypes', $outDataType)))
						{
							$semanticsTemplate=$this->get('Settings', 'semanticsTemplate');
							$this->core->debug(4, "callFeature: Applying --{$dataType['action']}={$dataType[$semanticsTemplate]}");
							$this->callFeature($dataType['action'], $dataType[$semanticsTemplate]);
							$dataType['chosenTemplate']=$dataType[$semanticsTemplate];
							$this->set('SemanticsState', 'currentDataType', $dataType);
							$this->set('SemanticsState', 'currentFeatureType', $obj['featureType']);
						}
						else $this->core->debug(4, "callFeature: Could not find dataType $outDataType");
					}
					else $this->core->debug(4, "callFeature: Could not find featureType ".$obj['featureType']);
				}
				
				
				if ($this->isVerboseEnough(4))
				{
					$resultCount=count($result);
					$nesting=$this->get('Core', 'nesting');
					$isArray=is_array($result)?'True':'False';;
					$this->debug(4, "INVOKE-Exit  {$indentation}{$obj['name']}/$nesting value={$value}, valueIn=$valueIn resultCount=$resultCount is_array=$isArray smCount=".$this->getResultSetCount());
					# $this->debugResultSet($obj['name']);
				}
				return $result;
			}
			else $this->complain(null, "Could not find a module to match '$argument'", 'callFeature');
		}
		else $this->debug(3,"Core->callFeature: Non executable code \"$argument\" sent. We shouldn't have got this.");
		return false;
	}
	
	function makeArgsAvailableToTheScript($featureName, $args)
	{
		$this->set('Global', $featureName, $args);
		foreach ($this->interpretParms($args) as $key=>$arg)
		{
			$this->set('Global', "$featureName-$key", $arg);
		}
	}
	
	function splitOnceOn($needle, $haystack)
	{
		if ($pos=strpos($haystack, $needle))
		{
			$first=substr($haystack, 0, $pos);
			$remaining=substr($haystack, $pos+strlen($needle));
			
			return array($first, $remaining);
		}
		else return array($haystack, '');

	}
	
	private function findAndProcessVariables($input, &$iterations=0)
	{
		$debugLevel=5;
		$output=$input;
		
		$this->core->debug($debugLevel, "findAndProcessVariables: Enter \"$output\"");
		
		while (strpos($output, storeValueBegin)!==false and $iterations<100)
		{
			$startPos=strpos($output, storeValueBegin)+2;
			$nextStartPos=strpos($output, storeValueBegin, $startPos)+2;
			$endPos=strpos($output, storeValueEnd, $startPos);
			
			if ($startPos>$endPos)
			{
				$oldStartPos=$startPos;
				$beginLen=strlen(storeValueBegin);
				if (substr($output, 0, $beginLen)== storeValueBegin)
				{
					$startPos=$beginLen-1;
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos oldStartPos=$oldStartPos Reset startPos");
				}
				else
				{
					$endPos=strpos($output, storeValueEnd, $startPos);
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Exended endPos (A)");
				}
			}
			
			$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Initial find from $output. ".storeValueBegin.' -> '.storeValueEnd);
			
			
			# This allows us to have nested variables.
			while ($nextStartPos<$endPos and $nextStartPos>$startPos)
			{
				$lastStart=$startPos;
				$startPos=strpos($output, storeValueBegin, $startPos)+2;
				$nextStartPos=strpos($output, storeValueBegin, $startPos)+2;
				
				# if ($startPos>$endPos) $startPos=$lastStart;
				if ($startPos>$endPos)
				{
					$endPos=strpos($output, storeValueEnd, $startPos);
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Exended endPos (B)");
				}
				# $this->core->debug(0, "Progressing $startPos $nextStartPos $endPos $output");
				$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Progressing using $output");
			}
			
			$length=$endPos-$startPos;
			
			$varDef=substr($output, $startPos, $length);
			$varParts=explode(',', $varDef);
			$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Trying to lookup $varDef from $output");
			
			if (isset($varParts[1]))
			{
				if (count($varParts)==2)
				{
					/*
						This will slowly become irrelevant as new features take advantage of nesting deeper than two levels. But until then it's still a huge performance saver.
					*/
					$varValue=$this->get($varParts[0], $varParts[1]);
				}
				else $varValue=$this->getNested($varParts);
				
				if (is_array($varValue))
				{
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Got array");
				}
				else
				{
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Got $varValue");
				}
				
				
				if (!is_array($varValue)) $output=implode($varValue, explode(storeValueBegin.$varDef.storeValueEnd, $output));
				else 
				{
					if (count($varValue))
					{
						$varValue=json_encode($varValue, JSON_FORCE_OBJECT);
						$output=implode($varValue, explode(storeValueBegin.$varDef.storeValueEnd, $output));
					}
					else $output=implode('', explode(storeValueBegin.$varDef.storeValueEnd, $output));
				}
			}
			else
			{
				$this->core->debug(0, "findAndProcessVariables: Probable syntax error in ".storeValueBegin."$varDef".storeValueEnd." . It should have had an extra value. Perhaps like this: ".storeValueBegin."Thing,{$varParts[0]}".storeValueEnd."");
			}
			
			$iterations++;
		}
		
		if ($iterations==100)
		{
			$this->debug(1, "Still finding \"".storeValueBegin."\" in \"".$output."\"");
		}
		
		$this->core->debug($debugLevel, "findAndProcessVariables: Return \"$output\"");
		
		return $output;
	}
	
	function processValue($value)
	{ // Substitute in an variables
		$output=$value;
		
		$iterations=0;
		
		$output=$this->findAndProcessVariables($output, $iterations);
		
		# This is the first go at escaping
		foreach (array(
			'~\!'=>'~!', 
			'!\~'=>'!~', 
			'~\%'=>'~%', 
			'%\~'=>'%~') as $from=>$to)
		{
			$output=implode($to, explode($from, $output));
		}
		
		$output=$this->findAndProcessVariables($output, $iterations);
		
		if ($iterations>10) $this->debug(0, "processValue: $iterations reached while processing variables in \"$value\". The result achieved is \"$output\". This is probably a big."); # NOTE 10 is very strict, yet unlikely to be reached in any legitimate situation I can think of at the moment. The warning limit may need to be reconsidered in the future.
		
		return $output;
	}
	
	function addAction($argument, $value=null, $macroName='default', $lineNumber=false)
	{
		if (!isset($this->store['Macros'])) $this->store['Macros']=array();
		if (!isset($this->store['Macros'][$macroName])) $this->store['Macros'][$macroName]=array();
		
		$obj=&$this->core->get('Features', $argument);
		if (is_array($obj))
		{
			$this->store['Macros'][$macroName][]=array('obj'=>&$obj, 'name'=>$obj['name'], 'value'=>$value, 'lineNumber'=>$lineNumber);
			$this->store['Features'][$argument]['referenced']++;
		}
		else
		{
			$macroDetails=($lineNumber)?"$macroName:$lineNumber":"$macroName";
			$this->complain(null, "Could not find a module to match '$argument' in $macroDetails", 'addAction');
		}

	}
	
	function debugResultSet($label='undefined')
	{
		if ($this->isVerboseEnough(4))
		{
			$nesting=$this->get('Core', 'nesting');
			$serial=$this->get('Core', 'serial');
			for ($i=$nesting;$i>-1;$i--)
			{
				$resultSet=$this->get('Core', 'shared'.$i);
				$this->debug(4, "debugResultSet $label/$i count=".count($resultSet)." serial=$serial");
			}
		}
	}
	
	function debug($verbosityLevel, $output)
	{
		if ($this->isVerboseEnough($verbosityLevel))
		{
			$title="debug$verbosityLevel";
			# TODO These lookups can be optimized!
			$code=$this->get('Codes', $title, false);
			$default=$this->get('Codes', 'default', false);
			$eol=$this->get('General', 'EOL', false); # TODO This can be improved
			echo "[$code$title$default]: $output$eol";
			# return false;
		}
	}
	
	function isVerboseEnough($verbosityLevel=0)
	{
		return ($this->verbosity >= $verbosityLevel);
	}
	
	function verbosity($level=0, $announce=true)
	{
		if (is_numeric($level))
		{
			$this->verbosity=intval($level);
			$verbosityName=$this->get('VerbosityLevel', $this->verbosity);
			if ($announce) $this->core->debug($this->verbosity, "verbosity: Set verbosity to \"$verbosityName\" ({$this->verbosity})");
		}
		elseif ($level=='-')
		{
			$this->verbosity=$this->verbosity-1;
			$verbosityName=$this->get('VerbosityLevel', $this->verbosity);
			if ($announce) $this->core->debug($this->verbosity, "verbosity: Decremented verbosity to \"$verbosityName\" ({$this->verbosity})");
		}
		else
		{
			$this->verbosity=$this->verbosity+1;
			$verbosityName=$this->get('VerbosityLevel', $this->verbosity, false);
			if ($announce) $this->core->debug($this->verbosity, "verbosity: Incremented verbosity to \"$verbosityName\" ({$this->verbosity})");
		}
		
		$this->set('Verbosity', 'level', $this->verbosity); // NOTE that changes to this variable will not affect the practicle verbosity. setRef coiuld be used, in which case changes would affect it. However we would then lack safety controls and events.
		
		if ($this->get('Features', 'triggerEvent')) $this->callFeature('triggerEvent', 'Verbosity,changed');
		else $this->debug(0, __CLASS__.'.'.__FUNCTION__.": triggerEvent is not defined. Perhaps an event handeler is not installed. It could be that it hasn't loaded yet.");
	}
	
	
	function incrementNesting()
	{
		# TODO Check: There may need to be some cleaning done here!
		$srcNesting=$this->get('Core', 'nesting');
		$nesting=(is_numeric($srcNesting))?$srcNesting+1:1;
		$this->set('Core', 'nesting', $nesting);
		$this->debug(5, "Incremented nesting to $nesting");
		$this->makeParentShareMemoryCurrent();
		return $nesting;
	}
	
	function decrementNesting()
	{
		$srcNesting=$this->get('Core', 'nesting');
		
		$this->delete(nestedPrivateVarsName, $srcNesting);
		
		$nesting=(is_numeric($srcNesting))?$srcNesting-1:1;
		if ($nesting<1) $nesting=1;
		$this->set('Core', 'nesting', $nesting);
		$this->debug(5, "Decremented nesting to $nesting count=".$this->getResultSetCount());
		return $nesting;
	}
	
	function getResultSetCount()
	{
		return count($this->getResultSet());
	}
	
	function &go($macroName='default')
	{
		$emptyResult=null;
		if (isset($this->store['Macros'][$macroName]))
		{
			if (count($this->store['Macros'][$macroName]))
			{
				# Set our shared memory location (this allows us to run macros within macros)
				$nesting=$this->incrementNesting();
				
				# Iterate through the actions to be taken
				foreach ($this->store['Macros'][$macroName] as $actionItem)
				{
					$nesting=$this->get('Core', 'nesting');
					$this->debug(5, "ITER $macroName/$nesting - {$actionItem['name']}: Result count before invoking=".count($this->getResultSet()));
					# $this->debugResultSet("$macroName - {$actionItem['name']}");
					
					# TODO The problem happens somewhere between here...
					$returnedValue1=$this->callFeature($actionItem['name'], $actionItem['value']);
					if (is_array($returnedValue1)) $returnedValue=$returnedValue1;
					# and here
					$this->debug(5,"GOT HERE ALSO");
					
					# $this->debugResultSet("$macroName - {$actionItem['name']}");
					
					$nesting=$this->get('Core', 'nesting');
					$this->debug(5, "ITER $macroName/$nesting - {$actionItem['name']}: Restult count before set=".count($this->getResultSet()));
					$this->setResultSet($returnedValue);
					$this->debug(5, "ITER $macroName/$nesting - {$actionItem['name']}: Result count after set=".count($this->getResultSet()));
					#echo "$macroName\n";
					#print_r($returnedValue);
				}
				$resultSet=$this->getResultSet();
				
				# Set the shared memory back to the previous nesting level
				$this->decrementNesting();
				
				# If this is the default macro, we need to run the cleanup stuff
				if ($macroName=='default')
				{
					$this->callFeature('triggerEvent', 'Mass,finishEarly');
					$this->callFeature('triggerEvent', 'Mass,finishGeneral');
					$this->callFeature('triggerEvent', 'Mass,finishLate');
				}
				
				return $resultSet;
			}
			else
			{
				$this->complain($this, "hmmmm, I don't think you asked me to do anything...");
				$obj=&$this->get('Features', 'helpDefault');
				$obj['obj']->event('helpDefault');
				return $emptyResult;
			}
		}
		else
		{
			$this->complain($this, "Could not find macro '$macroName'. This can happen if you haven't asked me to do anything.");
			
			$obj=&$this->get('Features', 'helpDefault');
			$obj['obj']->event('helpDefault');
			return $emptyResult;
		}
	}
	
	function &getCategoryModule($category)
	{
		if (isset($this->store[$category])) return $this->store[$category];
		else
		{
			$output=array();
			return $output;
		}
	}
	
	function setCategoryModule($category, $contents)
	{
		$this->store[$category]=$contents;
	}
	
	function unsetCategoryModule($category)
	{
		unset($this->store[$category]);
	}
	
	function &get($category, $valueName, $debug=true)
	{
		if ($debug) $this->debug(5,"get($category, $valueName, false)");
		#print_r($this->store);
		#echo "m=$category, v=$valueName\n";
		if (isset($this->store[$category]))
		{
			switch ($category)
			{
				case isolatedNestedPrivateVarsName:
					$nesting=$this->get('Core', 'nesting');
					if (isset($this->store[isolatedNestedPrivateVarsName][$nesting]))
					{
						if (isset($this->store[isolatedNestedPrivateVarsName][$nesting][$valueName]))
						{
							$result=$this->store[isolatedNestedPrivateVarsName][$nesting][$valueName];
						}
						else $result=null;
					}
					else $result=null;
					break;
				case nestedPrivateVarsName:
					$nesting=$this->get('Core', 'nesting');
					if (isset($this->store[nestedPrivateVarsName][$nesting])) 
					{
						for ($i=$nesting;$i>0;$i--)
						{
							if (isset($this->store[nestedPrivateVarsName][$i][$valueName]))
							{
								$result=$this->store[nestedPrivateVarsName][$i][$valueName];
								break;
							}
							else $result=null;
						}
					}
					else $result=null;
					break;
				default:
					if (isset($this->store[$category][$valueName])) return $this->store[$category][$valueName];
					else 
					{
						$result=null;
					}
					break;
			}
		}
		else
		{
			$result=null;
		}
		
		return $result;
	}
	
	function setIfNotSet($category, $valueName, $value, $orNothing=false)
	{
		$shouldSet=false;
		if ($category!=nestedPrivateVarsName)
		{
			if (!isset($this->store[$category])) $shouldSet=true;
			elseif (!isset($this->store[$category][$valueName])) $shouldSet=true;
			elseif (!$this->store[$category][$valueName] and $orNothing)  $shouldSet=true;
		}
		else
		{
			$nesting=$this->get('Core', 'nesting');
			if (!isset($this->store[$category])) $shouldSet=true;
			elseif(!isset($this->store[$category][$nesting])) $shouldSet=true;
			elseif (!isset($this->store[$category][$nesting][$valueName])) $shouldSet=true;
			elseif (!$this->store[$category][$nesting][$valueName] and $orNothing)  $shouldSet=true;
		}
		
		if ($shouldSet) $this->set($category, $valueName, $value);
	}
	
	function set($category, $valueName, $args)
	{ // set a variable for a module
		$argsDisplay=(is_array($args))?'Array':$args;
		$this->debug(5,"set($category, $valueName, $argsDisplay)");
		
		if (!isset($this->store[$category])) $this->store[$category]=array();
		
		if ($category!=nestedPrivateVarsName) $this->store[$category][$valueName]=$args;
		else
		{
			$nesting=$this->get('Core', 'nesting');
			if (!isset($this->store[$category][$nesting])) $this->store[$category][$nesting]=array();
			$this->store[$category][$nesting][$valueName]=$args;
		}
	}

	function setRef($category, $valueName, &$args)
	{ // set a variable for a module
		$argString=(is_string($args))?$argString:'[non-string]';
		$this->debug(5,"setRef($category, $valueName, $argString)");
		if (!isset($this->store[$category])) $this->store[$category]=array();
		
		if ($category!=nestedPrivateVarsName) $this->store[$category][$valueName]=&$args;
		else
		{
			$nesting=$this->get('Core', 'nesting');
			if (!isset($this->store[$category][$nesting])) $this->store[$category][$nesting]=array();
			$this->store[$category][$nesting][$valueName]=$args;
		}
	}
	
	function doUnSet($deleteList)
	{
		return $this->doUnsetNested($this->store, $deleteList);
	}
	
	function doUnsetNested(&$currentScope, $deleteList, $position=0)
	{
		if (!isset($currentScope[$deleteList[$position]]))
		{
			$fullChain=implode(',', $deleteList);
			$failedChain='';
			
			foreach ($deleteList as $item)
			{
				$failedChain=($failedChain)?"$failedChain,$item":$item;
			}
			
			$this->debug(3, "doUnsetNested: Could not find \"{$deleteList[$position]}\" in $fullChain. Successfully got to \"$failedChain\". This is unlikely to be a problem, but it worth considering when debugging.");
			return false;
		}
		
		$fullChain=implode(',', $deleteList);
		$newPosition=$position+1;
		if (count($deleteList)>$newPosition)
		{ // Recurse into the currentScope
			$this->debug(4, "doUnsetNested: Recuring for {$deleteList[$position]} in $fullChain");
			return $this->doUnsetNested($currentScope[$deleteList[$position]], $deleteList, $newPosition);
		}
		else
		{ // Time to delete
			$this->debug(4, "doUnsetNested: Deleting {$deleteList[$position]} in $fullChain");
			unset($currentScope[$deleteList[$position]]);
			return true;
		}
	}
	
	function getNested($values)
	{
		$output=$this->store;
		foreach ($values as $value)
		{
			if (isset($output[$value]))
			{
				$output=$output[$value];
				# print_r($output);
			}
			else
			{
				$this->core->debug(4, "getNested: Could not find \"$value\" using key ".implode(',', $values));
				return false;
			}
		}
		
		return $output;
	}
	
	function setNested($store, $category, $values)
	{
		$initialValue=$this->get($store, $category);
		if (!is_array($initialValue)) $initialValue=array();
		
		# $this->debug(0, "setNested($store, $category, ".json_encode($values).")");
		
		$this->set($store, $category, $this->setNestedRecursively($initialValue, $values, count($values)));
	}
	
	function setNestedRecursively($existingArray, $values, $valueCount, $progress=0)
	{
		if ($progress<$valueCount-1)
		{
			# TODO I suspect the problem of the extra empty array is with this.
			if (!isset($existingArray[$values[$progress]])) $existingArray[$values[$progress]]=array();
			if (!is_array($existingArray[$values[$progress]])) $existingArray[$values[$progress]]=array();
			
			if ($values[$progress] != '' or is_numeric($values[$progress]))
			{
				# $this->debug(0, "setNestedRecursively(".json_encode($existingArray).", ".json_encode($values).", $valueCount, $progress=0) - keyed ($values[$progress])");
				$existingArray[$values[$progress]]=$this->setNestedRecursively($existingArray[$values[$progress]], $values, $valueCount, $progress+1);
			}
			else
			{
				# $this->debug(0, "setNestedRecursively(".json_encode($existingArray).", ".json_encode($values).", $valueCount, $progress=0) - incremeted ($values[$progress])");
				$existingArray[]=$this->setNestedRecursively($existingArray[$values[$progress]], $values, $valueCount, $progress+1);
			}
			
			return $existingArray;
		}
		else
		{
			# $this->debug(0, "setNestedRecursively(".json_encode($existingArray).", ".json_encode($values).", $valueCount, $progress=0) - final");
			return $values[$progress];
		}
	}
	
	function addItemsToAnArray($category, $valueName, $items)
	{
		$currentList=$this->get($category, $valueName);
		if (is_array($currentList)) $output=array_merge($currentList, $items);
		else $output=$items;
		
		$this->set($category, $valueName, $output);
		return $output;
	}
	
	function delete($category, $valueName)
	{
		if (isset($this->store[$category]))
		{
			if (isset($this->store[$category][$valueName])) 
			{
				unset($this->store[$category][$valueName]);
				$this->debug(5,"delete($category, $valueName) - deleted");
			}
			else  $this->debug(5,"delete($category, $valueName) - valueName not found");
		}
		else $this->debug(5,"delete($category, $valueName) - category not found");
	}
	
	function getStore()
	{ # Note that this returns a COPY of the store. It is not intended as a way of modifying the store.
		$this->debug(5,"getStore()");
		return $this->store;
	}
	
	function registerModule(&$obj)
	{
		$name=$obj->getName();
		if (isset($this->module[$name]))
		{
			echo "Module $name is already loaded.\n";
			return false;
		}
		
		$this->module[$name]=&$obj;
		$this->module[$name]->setCore($this);
		return true;
	}
	
	function registerFeature(&$obj, $flags, $name, $description, $tags=false,$isMacro=false, $source='unknown')
	{
		$this->core->debug(4, "registerFeature name=$name");
		$arrayTags=(is_array($tags))?$tags:explode(',', $tags);
		if (!count($arrayTags))
		{
			$arrayTags[]='undefined';
		}
		// $arrayTags[]=$name; # I'm not convinced this is a good item. It means we are going to have a stupid amount of tags that are only used once.
		$arrayTags[]='all';
		$arrayTags[]=$obj->getName();
		$this->registerTags($name, $arrayTags);
		$tagString=implode(',', $arrayTags);
		
		# TODO Remove the tag string from descriptoin once we have proper integration with help
		$entry=array('obj'=>&$obj, 'flags'=>$flags, 'name'=>$name, 'description'=>$description, 'tagString'=>$tagString, 'provider'=>$obj->getName(), 'isMacro'=>$isMacro, 'source'=>$source, 'referenced'=>0);
		
		foreach ($flags as $flag)
		{
			if (!isset($this->store['Features'][$flag]))
			{
				$this->setRef('Features', $flag, $entry);
			}
			else
			{
				$existing=$this->get('Features', $flag);
				$existingName=$existing['obj']->getName();
				$this->complain($obj, "Feature $flag has already been registered by $existingName");
			}
		}
	}
	
	function aliasFeature($feature, $flags)
	{
		$entry=&$this->get('Features', $feature);
		foreach ($flags as $flag)
		{
			if (!isset($this->store['Features'][$flag]))
			{
				$this->core->debug(4, "Aliasing $flag => $feature");
				$this->setRef('Features', $flag, $entry);
				$entry['flags'][]=$flag;
			}
			elseif ($flag==$feature)
			{}
			else
			{
				$existing=$this->get('Features', $flag);
				$existingName=$existing['obj']->getName();
				$this->complain($this, "Feature $flag has already been registered by $existingName");
			}
		}
	}
	
	function setFeatureAttribute($featureName, $attributeName, $attributeValue)
	{
		$entry=&$this->get('Features', $featureName);
		$entry[$attributeName]=$attributeValue;
	}
	
	function registerTags($name, $tags)
	{
		$arrayTags=(is_array($tags))?$tags:explode(',', $tags);
		foreach ($arrayTags as $tag)
		{
			if ($tag)
			{
				$names=$this->get('Tags', $tag);
				if (!is_array($names)) $names=array();
				
				$names[]=$name;
				$this->set('Tags', $tag, $names);
			}
		}
	}
	
	function callInits($event='init')
	{
		# TODO The initMap is not working. Symptom: the Packages module (and potentially others) get inited twice. I've worked around this by putting in a condition around the $callInits variable in the loadModules function. This situation arises when calling loadModules from the packages.php module. 
		if (!isset($this->initMap[$event])) $this->initMap[$event]=array();
		foreach ($this->module as $name=>&$obj)
		{
			if (!isset($this->initMap[$event][$name]))
			{
				$obj->event($event);
				$this->initMap[$event][$name]=true;
			}
		}
	}
	
	function complain($obj, $message, $specific='', $fatal=false)
	{
		$output=($specific)?"$specific: $message":"$message.";
		if ($obj) $output=$obj->getName().': '.$output;
		
		if ($fatal) die("$output\n");
		else $this->debug(0, "$output");
	}
	
	function requireNumParms($obj, $numberRequried, $event, $originalParms, $interpretedParms=false)
	{
		$parmsToCheck=($interpretedParms)?$interpretedParms:$this->interpretParms($originalParms);
		$actualParms=count($parmsToCheck);
		
		if ($numberRequried>$actualParms) 
		{
			$this->complain($obj, "Required $numberRequried parameters but got $actualParms. Original parms were \"$originalParms\" for", $event, true);
			return false;
		}
		else return true;
	}
	
	function getRequireNumParmsOrComplain($obj, $featureName, $numberRequried)
	{
		$originalParms=$this->get('Global', $featureName);
		$interpretedParms=$this->interpretParms($originalParms);
		
		if ($this->requireNumParms($obj, $numberRequried, $featureName, $originalParms, $interpretedParms))
		{
			return $interpretedParms;
		}
		else
		{
			return false;
		}
	}
	
	function out($output)
	{
		if (isset($this->store['General']['outputObject']))
		{
			$this->store['General']['outputObject']->out($output);
		}
		else
		{
			if (is_string($output)) echo programName."/noOut: $output\n";
			else
			{
				echo programName."/noOut: print_r output follows:\n";
				print_r($output);
			}
		}
	}
	
	function echoOut($output)
	{
		if (isset($this->store['General']['echoObject']))
		{
			$this->store['General']['echoObject']->put(array($output), 'default');
		}
		else
		{
			if (is_string($output)) echo programName."/noEcho: $output\n";
			else
			{
				echo programName."/noEcho: print_r output follows:\n";
				print_r($output);
			}
		}
	}
	
	function now()
	{
		return 'I need to implement this!';
	}
	
	function getPID($parms)
	{
		$this->set($parms[0], $parms[1], strval(getmypid()));
	}
	
	function objectToArray($data)
	{
		if (!is_array($data) and !is_object($data)) return $data;
		$result = array();
		
		$data = (array) $data;
		foreach ($data as $key => $value)
		{
			$newKey=$this->cleanKey($key);
			if (is_object($value) or is_array($value)) $result[$newKey] = $this->objectToArray($value);
			else $result[$newKey] = $value;
		}
		
		return $result;
	}
	
	function cleanKey($key)
	{
		$result=str_replace("\0", '', $key);
		$result=preg_replace(array('/^\*_/'), array(''), $result);
		return $result;
	}
}

function loadModules(&$core, $sourcePath, $callInits=true)
{
	foreach ($core->getModules($sourcePath) as $path)
	{
		$path=$path;
		if (file_exists($path))
		{
			#echo "Loading $path\n";
			$filenameParts=explode('.', $path);
			$numParts=count($filenameParts);
			$lastPos=($numParts>1)?$numParts-1:0;
			
			if ($filenameParts[$lastPos]=='php'or $filenameParts[$lastPos]=='module')
			{
				include ($path);
			}
		}
		else
		{
			echo "Didn't find $path\n";
		}
	}
	
	if ($callInits)
	{
		$core->callInits(); // Basic init only
		$core->callInits('followup'); // Any action that needs to be taken once all modules are loaded.
		$core->callInits('last'); // Any action that needs to be taken once all modules are loaded.
	}
}




class Module
{
	private $category=''; 
	protected $core=null;
	
	function __construct($name)
	{
		$this->category=$name;
	}
	
	function getName()
	{
		return $this->category;
	}
	
	function setCore(&$core)
	{
		$this->core=&$core;
	}
}
 
?>