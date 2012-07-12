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

define('workAroundIfBug', true); // See doc/bugs/ifBug.md


/*
	Debug levele
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
	
	function __construct()
	{
		$this->store=array();
		$this->module=array();
		
		parent::__construct('Core');
		$this->set('Core', 'serial', intval(rand()));
		$this->registerModule(&$this);
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
				# $this->registerFeature($this, array('get'), 'get', 'Get a value. --get=moduleName'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('getToResult', 'get'), 'getToResult', 'Get a value and put it in an array so we can do stuff with it. --getToResult=moduleName'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('set'), 'set', 'set a value. All remaining values after the destination go into a string. --set=moduleName'.valueSeparator.'variableName'.valueSeparator.'value', array('storeVars'));
				$this->registerFeature($this, array('setArray'), 'setArray', 'set a value. All remaining values after the destination go into an array. --set=moduleName'.valueSeparator.'variableName'.valueSeparator.'value', array('storeVars'));
				$this->registerFeature($this, array('setIfNotSet', 'setDefault'), 'setIfNotSet', 'set a value if none has been set. --setIfNotSet=moduleName'.valueSeparator.'variableName'.valueSeparator.'defaultValue', array('storeVars'));
				$this->registerFeature($this, array('getStore'), 'getStore', 'Get an entire store into the result set. --getStore=moduleNam', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('setStore'), 'setStore', 'Set an entire store to the current state of the result set. --setStore=moduleName', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('stashResults'), 'stashResults', 'Put the current result set into a memory slot. --stashResults=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('retrieveResults'), 'retrieveResults', 'Retrieve a result set that has been stored. This will replace the current result set with the retrieved one --retrieveResults=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('getPID'), 'getPID', 'Save the process ID to a variable. --getPID=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('setJson'), 'setJson', 'Take a json encoded array from jsonValue and store the arrary in moduleName'.valueSeparator.'variableName. --setJson=moduleName'.valueSeparator.'variableName'.valueSeparator.'jsonValue');
				$this->registerFeature($this, array('outNow'), 'outNow', 'Execute the output now.', array('dev'));
				$this->registerFeature($this, array('dump'), 'dump', 'Dump internal state.', array('debug', 'dev'));
				$this->registerFeature($this, array('debug'), 'debug', 'Send parameters to stdout. --debug=debugLevel,outputText eg --debug=0,StuffToWriteOut . DebugLevel is not implemented yet, but 0 will be "always", and above that will only show as the verbosity level is incremented with -v or --verbose.', array('debug', 'dev'));
				$this->registerFeature($this, array('verbose', 'v'), 'verbose', 'Increment/set the verbosity. --verbose[=verbosityLevel] where verbosityLevel is an integer starting from 0 (default)', array('debug', 'dev'));
				$this->registerFeature($this, array('ping'), 'ping', 'Useful for debugging.', array('debug', 'dev'));
				$this->registerFeature($this, array('#'), '#', 'Comment.');
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
			case 'getToResult':
				$parms=$this->interpretParms($this->get('Global', 'getToResult'));
				return array($this->get($parms[0], $parms[1]));
				break;
			case 'set':
				$parms=$this->interpretParms($this->get('Global', 'set'), 2, true);
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'setArray':
				$parms=$this->interpretParms($this->get('Global', 'setArray'), 2, false);
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'setIfNotSet':
				$originalParms=$this->get('Global', 'setIfNotSet');
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 2, $event, $originalParms, $parms);
				$this->setIfNotSet($parms[0], $parms[1], $parms[2]);
				break;
			case 'getStore':
				return $this->getStoreModule($this->get('Global', 'getStore'));
				break;
			case 'setStore':
				$this->setStoreModule($this->get('Global', 'setStore'), $this->getSharedMemory());
				break;
			case 'stashResults':
				$originalParms=$this->get('Global', 'stashResults');
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 2, $event, $originalParms, $parms);
				$this->set($parms[0], $parms[1], $this->core->getSharedMemory());
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
			case 'debug':
				$originalParms=$this->get('Global', 'debug');
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 2, $event, $originalParms, $parms);
				$this->debug($parms[0], $parms[1]);
				return false;
				break;
			case 'verbose':
				$original=$this->get('Global', 'verbose');
				$this->verbosity($original);
				break;
			case 'getPID':
				$this->getPID($this->interpretParms($this->get('Global', 'getPID')));
				break;
			case 'ping':
				echo "Pong.\n";
				break;
			case 'outNow':
				$this->out($this->getSharedMemory());
				break;
			case '#':
				break;
			default:
				$this->complain($this, 'Unknown event', $event);
				break;
		}
	}

	public static function assert()
	{
		if (!self::$singleton) self::$singleton=new core();
		return self::$singleton;
	}
	
	function interpretParms($parms, $limit=0, $reassemble=true)
	{
		$parts=explode(valueSeparator, $parms);
		
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
				else return $output;
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
			return $output;
		}
		else return $parts;
	}
	
	function getFileList($path)
	{
		# TODO This can be done much better internally in PHP
		$output=array();
		$files=explode("\n", `ls -1 $path`);
		foreach ($files as $file)
		{
			$trimmedFile=trim($file);
			if ($trimmedFile) $output[$trimmedFile]="$path/$trimmedFile";
		}
		return $output;
	}
	
	function getModules($path)
	{ // get all the module paths from a path
		return $this->getFileList($path);
	}
	
	function setSharedMemory(&$value, $src='unknown')
	{
		$this->debug(5, "setSharedMemory(value=$value, src=$src)");
		if (is_array($value)) # ($value!=null and $value!==false)
		{
			$nesting=$this->get('Core', 'nesting');
			if ($this->isVerboseEnough(5))
			{
				$numberOfEntries=count($value);
				$this->debug(5, "setSharedMemory(value=$value($numberOfEntries), src=$src)/$nesting - is_array == true. VALUE WILL BE SET");
				if ($this->isVerboseEnough(6)) 
				{
					print_r($value);
					$this->debug(6, "setSharedMemory(value=$value($numberOfEntries), src=$src)/$nesting - exiting from var dump.");
				}
				$serial=$this->get('Core', 'serial');
				$this->debugSharedMemory("setSharedMemory $src/$serial");
			}
			$this->setRef('Core', 'shared'.$nesting, $value);
			return true;
		}
		else return false;
	}
	
	function &getSharedMemory()
	{
		$nesting=$this->get('Core', 'nesting');
		$sharedMemoryDiag=count($this->get('Core', 'shared'.$nesting));
		if ($this->isVerboseEnough(5))
		{
			$serial=$this->get('Core', 'serial');
			$this->debug(5, "getSharedMemory/$nesting count=$sharedMemoryDiag serial=$serial");
			#print_r($this->get('Core', 'shared'.$nesting));
		}
		return $this->get('Core', 'shared'.$nesting);
	}
	
	function &getParentSharedMemory()
	{
		$nesting=$this->get('Core', 'nesting');
		$nestingSrc=$nesting-1; # TODO Check this. Adding this fixes the shared memory loss. MEMORY BUG
		if ($nestingSrc<1 or !is_numeric($nestingSrc)) $nestingSrc = 1; # TODO check this
		$sharedMemory=&$this->get('Core', 'shared'.$nestingSrc);
		
		# NOTE I think this is the source of the bug that has been kicking my arse! MEMORY BUG
		#if (!is_array($sharedMemory)) $sharedMemory=array();
		
		if ($this->isVerboseEnough(5))
		{
			$serial=$this->get('Core', 'serial');
			$sharedMemoryDiag=count($sharedMemory);
			$this->debug(5, "getParentSharedMemory $nestingSrc->$nesting/$sharedMemoryDiag/$serial");
		}
		return $sharedMemory;
	}
	
	function makeParentShareMemoryCurrent()
	{
		$this->debug(5, "makeParentShareMemoryCurrent/");
		$this->setSharedMemory($this->getParentSharedMemory());
	}
	
	function triggerEvent($argument, $value)
	{
		$nesting=$this->get('Core', 'nesting');
		if ($argument=='	')
		{
			$argument=$this->get('Core', 'lastArgument'.$nesting);
			$lastValue=$this->get('Core', 'lastValue'.$nesting);
			$value=($lastValue)?$lastValue.','.$value:$value;
		}
		else
		{
			$this->set('Core', 'lastArgument'.$nesting, $argument);
			$this->set('Core', 'lastValue'.$nesting, $value);
		}
		
		if ($argument and $argument != '#' and $argument != '//')
		{ // Only process non-white space
			$obj=&$this->core->get('Features', $argument);
			if (is_array($obj))
			{
				$indentation=str_repeat('  ', $nesting);
				$valueIn=$this->processValue($value);
				
				$this->debug(3, "INVOKE-Enter {$indentation}{$obj['name']}/$nesting value={$value}, valueIn=$valueIn");
				
				if ($this->isVerboseEnough(5))
				{
					$this->debugSharedMemory($obj['name']);
				}
				
				$this->set('Global', $obj['name'], $valueIn);
				$result=$obj['obj']->event($obj['name']);
				
				if ($this->isVerboseEnough(4))
				{
					$resultCount=count($result);
					$nesting=$this->get('Core', 'nesting');
					$isArray=is_array($result)?'True':'False';;
					$this->debug(4, "INVOKE-Exit  {$indentation}{$obj['name']}/$nesting value={$value}, valueIn=$valueIn resultCount=$resultCount is_array=$isArray smCount=".$this->getSharedMemoryCount());
					$this->debugSharedMemory($obj['name']);
				}
				$this->debug(3,'GOT HERE');
				return $result;
			}
			else $this->complain(null, "Could not find a module to match '$argument'", 'triggerEvent');
		}
		return false;
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
	
	function processValue($value)
	{ // Substitute in an variables
		$output=$value;
		
		while (strpos($output, storeValueBegin)!==false)
		{
			$startPos=strpos($output, storeValueBegin)+2;
			$endPos=strpos($output, storeValueEnd);
			$length=$endPos-$startPos;
			
			$varDef=substr($output, $startPos, $length);
			$varParts=explode(',', $varDef);
			$varValue=$this->get($varParts[0], $varParts[1]);
			$output=implode($varValue, explode(storeValueBegin.$varDef.storeValueEnd, $output));
		}
		
		return $output;
	}
	
	function processSingleResult($input)
	{
		for ($i=0;$i<50;$i++)
		{
			
			
			# iterate through array
		}
		return $output;
	}
	
	function addAction($argument, $value=null, $macroName='default')
	{
		if (!isset($this->store['Macros'])) $this->store['Macros']=array();
		if (!isset($this->store['Macros'][$macroName])) $this->store['Macros'][$macroName]=array();
		
		$obj=&$this->core->get('Features', $argument);
		if (is_array($obj))
		{
			$this->store['Macros'][$macroName][]=array('obj'=>&$obj, 'name'=>$obj['name'], 'value'=>$value);
		}
		else $this->complain(null, "Could not find a module to match '$argument'", 'addAction');

	}
	
	function debugSharedMemory($label='undefined')
	{
		$nesting=$this->get('Core', 'nesting');
		$serial=$this->get('Core', 'serial');
		for ($i=$nesting;$i>-1;$i--)
		{
			$sharedMemory=$this->get('Core', 'shared'.$i);
			$this->debug(3, "debugSharedMemory $label/$i count=".count($sharedMemory)." serial=$serial");
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
			echo "[$code$title$default]: $output\n";
			# return false;
		}
	}
	
	function isVerboseEnough($verbosityLevel=0)
	{
		return ($this->verbosity >= $verbosityLevel);
	}
	
	function verbosity($level=0)
	{
		if (is_numeric($level)) $newlevel=intval($level);
		else
		{
			$this->verbosity=$this->verbosity+1;
		}
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
		$nesting=(is_numeric($srcNesting))?$srcNesting-1:1;
		if ($nesting<1) $nesting=1;
		$this->set('Core', 'nesting', $nesting);
		$this->debug(5, "Decremented nesting to $nesting count=".$this->getSharedMemoryCount());
		return $nesting;
	}
	
	function getSharedMemoryCount()
	{
		return count($this->getSharedMemory());
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
					$this->debug(5, "ITER $macroName/$nesting - {$actionItem['name']}: Result count before invoking=".count($this->getSharedMemory()));
					$this->debugSharedMemory("$macroName - {$actionItem['name']}");
					
					# TODO The problem happens somewhere between here...
					$returnedValue1=$this->triggerEvent($actionItem['name'], $actionItem['value']);
					if (is_array($returnedValue1)) $returnedValue=$returnedValue1;
					# and here
					$this->debug(5,"GOT HERE ALSO");
					
					$this->debugSharedMemory("$macroName - {$actionItem['name']}");
					
					$nesting=$this->get('Core', 'nesting');
					$this->debug(5, "ITER $macroName/$nesting - {$actionItem['name']}: Restult count before set=".count($this->getSharedMemory()));
					$this->setSharedMemory($returnedValue);
					$this->debug(5, "ITER $macroName/$nesting - {$actionItem['name']}: Result count after set=".count($this->getSharedMemory()));
					#echo "$macroName\n";
					#print_r($returnedValue);
				}
				$sharedMemory=$this->getSharedMemory();
				
				# Output our results if we are back to the first level
				if ($nesting==1)
				{
					$this->out($sharedMemory);
				}
				
				# Set the shared memory back to the previous nesting level
				$this->decrementNesting();
				
				return $sharedMemory;
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
	
	function &getStoreModule($moduleName)
	{
		if (isset($this->store[$moduleName])) return $this->store[$moduleName];
		else return array();
	}
	
	function setStoreModule($moduleName, $contents)
	{
		$this->store[$moduleName]=$contents;
	}
	
	function &get($moduleName, $valueName, $debug=true)
	{
		if ($debug) $this->debug(5,"get($moduleName, $valueName)");
		#print_r($this->store);
		#echo "m=$moduleName, v=$valueName\n";
		if (isset($this->store[$moduleName]))
		{
			if (isset($this->store[$moduleName][$valueName])) return $this->store[$moduleName][$valueName];
			else 
			{
				$result=null;
			}
		}
		{
			$result=null;
		}
		
		return $result;
	}
	
	function setIfNotSet($moduleName, $valueName, $value)
	{
		$shouldSet=false;
		if (!isset($this->store[$moduleName])) $shouldSet=true;
		elseif (!isset($this->store[$moduleName][$valueName])) $shouldSet=true;
		
		if ($shouldSet) $this->set($moduleName, $valueName, $value);
	}
	
	function set($moduleName, $valueName, $args)
	{ // set a variable for a module
		$this->debug(5,"set($moduleName, $valueName, $args)");
		if (!isset($this->store[$moduleName])) $this->store[$moduleName]=array();
		
		$this->store[$moduleName][$valueName]=$args;
	}

	function setRef($moduleName, $valueName, &$args)
	{ // set a variable for a module
		$argString=(is_string($args))?$argString:'[non-string]';
		$this->debug(5,"setRef($moduleName, $valueName, $argString)");
		if (!isset($this->store[$moduleName])) $this->store[$moduleName]=array();
		
		$this->store[$moduleName][$valueName]=&$args;
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
	
	function registerFeature(&$obj, $flags, $name, $description, $tags=false)
	{
		$this->core->debug(4, "registerFeature name=$name, tags=$tags");
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
		$entry=array('obj'=>&$obj, 'flags'=>$flags, 'name'=>$name, 'description'=>$description, 'tagString'=>$tagString);
		
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
			}
			elseif ($flag==$feature)
			{}
			else
			{
				$existing=$this->get('Features', $flag);
				$existingName=$existing['obj']->getName();
				$this->complain($obj, "Feature $flag has already been registered by $existingName");
			}
		}
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
		foreach ($this->module as $name=>&$obj)
		{
			$obj->event($event);
		}
	}
	
	function complain($obj, $message, $specific='', $fatal=false)
	{
		$output=($specific)?"$message \"$specific\"":"$message.";
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
	
	function now()
	{
		return 'I need to implement this!';
	}
	
	function getPID($parms)
	{
		$this->set($parms[0], $parms[1], strval(getmypid()));
	}
}

function loadModules(&$core, $sourcePath)
{
	foreach ($core->getModules($sourcePath) as $path)
	{
		$path=$path;
		if (file_exists($path))
		{
			#echo "Loading $path\n";
			include ($path);
		}
		else
		{
			echo "Didn't find $path\n";
		}
	}
	
	$core->callInits(); // Basic init only
	$core->callInits('followup'); // Any action that needs to be taken once all modules are loaded.
	$core->callInits('last'); // Any action that needs to be taken once all modules are loaded.
}




class Module
{
	private $moduleName=''; 
	protected $core=null;
	
	function __construct($name)
	{
		$this->moduleName=$name;
	}
	
	function getName()
	{
		return $this->moduleName;
	}
	
	function setCore(&$core)
	{
		$this->core=&$core;
	}
}
 
?>