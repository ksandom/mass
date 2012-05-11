<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

define('valueSeparator', ',');
define('storeValueBegin', '~!');
define('storeValueEnd', '!~');

class core extends Module
{
	private $store;
	private $module;
	private static $singleton;
	
	function __construct()
	{
		$this->store=array();
		$this->module=array();
		
		parent::__construct('Core');
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
				$this->registerFeature($this, array('get'), 'get', 'Get a value. --get=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('set'), 'set', 'set a value. --set=moduleName'.valueSeparator.'variableName'.valueSeparator.'value');
				$this->registerFeature($this, array('stashResults'), 'stashResults', 'Put the current result set into a memory slot. --stashResults=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('retrieveResults'), 'retrieveResults', 'Retrieve a result set that has been stored. This will replace the current result set with the retrieved one --retrieveResults=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('getPID'), 'getPID', 'Save the process ID to a variable. --getPID=moduleName'.valueSeparator.'variableName');
				$this->registerFeature($this, array('setJson'), 'setJson', 'Take a json encoded array from jsonValue and store the arrary in moduleName'.valueSeparator.'variableName. --setJson=moduleName'.valueSeparator.'variableName'.valueSeparator.'jsonValue');
				$this->registerFeature($this, array('dump'), 'dump', 'Dump internal state.');
				$this->registerFeature($this, array('ping'), 'ping', 'Useful for debugging.');
				$this->registerFeature($this, array('#'), '#', 'Comment.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'get':
				$parms=$this->interpretParms($this->get('Global', 'get'));
				return $this->get($parms[0], $parms[1]);
				break;
			case 'set':
				$parms=$this->interpretParms($this->get('Global', 'set'));
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'stashResults':
				$parms=$this->interpretParms($this->get('Global', 'stashResults'));
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
			case 'getPID':
				$this->getPID($this->interpretParms($this->get('Global', 'getPID')));
				break;
			case 'ping':
				echo "Pong.\n";
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
	
	function interpretParms($parms)
	{
		return explode(valueSeparator, $parms);
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
	
	function setSharedMemory(&$value)
	{
		if ($value!=null and $value!==false)
		{
			$nesting=$this->get('Core', 'nesting');
			$this->setRef('Core', 'shared'.$nesting, $value);
			return true;
		}
		else return false;
	}
	
	function &getSharedMemory()
	{
		$nesting=$this->get('Core', 'nesting');
		return $this->get('Core', 'shared'.$nesting);
	}
	
	function &getParentSharedMemory()
	{
		$nesting=$this->get('Core', 'nesting');
		if ($nesting<1 or !is_numeric($nesting)) $nesting = 1; # TODO check this
		$sharedMemory=&$this->get('Core', 'shared'.$nesting);
		if (!is_array($sharedMemory)) $sharedMemory=array();
		return $sharedMemory;
	}
	
	function makeParentShareMemoryCurrent()
	{
		$this->setSharedMemory($this->getParentSharedMemory());
	}
	
	function triggerEvent($argument, $value)
	{
		if ($argument and $argument != '#' and $argument != '//')
		{ // Only process non-white space
			$obj=&$this->core->get('Features', $argument);
			if (is_array($obj))
			{
				$this->set('Global', $obj['name'], $this->processValue($value));
				return $obj['obj']->event($obj['name']);
			}
			else $this->complain(null, "Could not find a module to match '$argument'", 'triggerEvent');
		}
		return false;
	}
	
	function splitOnceOn($needle, $haystack)
	{
		echo "h='$haystack'";
		if ($pos=strpos($haystack, $needle))
		{
			$first=substr($haystack, 0, $pos);
			$remaining=substr($haystack, $pos+strlen($needle));
			
			echo "f='$first' r='$remaining'\n";
			
			return array($first, $remaining);
		}
		else return array($haystack, '');

	}
	
	function processValue($value)
	{ // Substitute in an variables
		$output=$value;
		
		while (strpos($output, '~!')!==false)
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
		echo "debugSharedMemory $label ".count($this->getSharedMemory())."\n";
	}
	
	function &go($macroName='default')
	{
		$emptyResult=null;
		if (isset($this->store['Macros'][$macroName]))
		{
			if (count($this->store['Macros'][$macroName]))
			{
				# Set our shared memory location (this allows us to run macros within macros)
				$nesting=$this->get('Core', 'nesting');
				$nesting=(is_numeric($nesting))?$nesting+1:1;
				$this->set('Core', 'nesting', $nesting);
				
				$this->makeParentShareMemoryCurrent();
				
				# Iterate through the actions to be taken
				foreach ($this->store['Macros'][$macroName] as $actionItem)
				{
					$returnedValue=$this->triggerEvent($actionItem['name'], $actionItem['value']);
					$this->setSharedMemory($returnedValue);
					#echo "$macroName\n";
					#print_r($returnedValue);
				}
				$sharedMemory=&$this->getSharedMemory();
				
				# Output our results if we are back to the first level
				if ($nesting==1)
				{
					$this->out($sharedMemory);
				}
				
				# Set the shared memory back to the previous nesting level
				$nesting--;
				$this->set('Core', 'nesting', $nesting);
				
				return $sharedMemory;
			}
			else
			{
				$this->complain($this, "hmmmm, I don't think you asked me to do anything...");
				$obj=&$this->get('Features', 'help');
				$obj['obj']->event('help');
				return $emptyResult;
			}
		}
		else
		{
			$this->complain($this, "Could not find macro '$macroName'. This can happen if you haven't asked me to do anything.");
			
			$obj=&$this->get('Features', 'help');
			$obj['obj']->event('help');
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
	
	function &get($moduleName, $valueName)
	{
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
	
	function set($moduleName, $valueName, $args)
	{ // set a variable for a module
		if (!isset($this->store[$moduleName])) $this->store[$moduleName]=array();
		
		$this->store[$moduleName][$valueName]=$args;
	}

	function setRef($moduleName, $valueName, &$args)
	{ // set a variable for a module
		if (!isset($this->store[$moduleName])) $this->store[$moduleName]=array();
		
		$this->store[$moduleName][$valueName]=&$args;
	}
	
	function getStore()
	{ # Note that this returns a COPY of the store. It is not intended as a way of modifying the store.
		return $this->store;
	}

	function run($moduleName, $function, $args=null)
	{ // Run code of a module
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
		$entry=array('obj'=>&$obj, 'flags'=>$flags, 'name'=>$name, 'description'=>$description." ~ $tagString");
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
		$output=($specific)?"$message: $specific":"$message.";
		if ($obj) $output=$obj->getName().$output;
		
		if ($fatal) die("$output\n");
		else echo "$output\n";
	}
	
	function requireNumParms($obj, $numberRequried, $event, $originalParms, $interpretedParms=false)
	{
		$parmsToCheck=($interpretedParms)?$interpretedParms:$this->interpretParms($originalParms);
		$actualParms=count($parmsToCheck);
		
		if ($numberRequried>$actualParms) $this->complain($obj, "Required $numberRequried parameters but got $actualParms. Original parms were \"$originalParms\" for", $event, true);
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