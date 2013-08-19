<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Useful stuff for manipulating the core
define('macroLineTerminator', ';');

class Macro extends Module
{
	private $lastCreatedMacro=null;
	
	function __construct()
	{
		parent::__construct('Macro');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('singleLineMacro'), 'singleLineMacro', 'Define and run a macro. --macro=macroName:"command1=blah;command2=wheee"');
				$this->core->registerFeature($this, array('macro'), 'macro', 'Define and run a macro. --macro=macroName:"command1=blah\ncommand2=wheee"');
				$this->core->registerFeature($this, array('defineSingleLineMacro'), 'defineSingleLineMacro', 'Define a macro. --defineMacro=macroName:"command1=blah;command2=wheee"');
				$this->core->registerFeature($this, array('defineMacro'), 'defineMacro', 'Define a macro. --defineMacro=macroName:"command1=blah\ncommand2=wheee"');
				$this->core->registerFeature($this, array('runMacro'), 'runMacro', 'Run a macro. --runMacro=macroName');
				$this->core->registerFeature($this, array('listMacros'), 'listMacros', 'List all macros');
				$this->core->registerFeature($this, array('loop', 'loopMacro'), 'loop', 'Loop through a resultSet. The current iteration of the resultSet is accessed via STORE variables under the category Result. See loopMacro.md for more information. --loop=macroName[,parametersForTheMacro]', array('loop', 'iterate', 'resultset')); # TODO This should probably move to a language module
				$this->core->registerFeature($this, array('forEach'), 'forEach', "For each result in the resultSet, run this command. The whole resultSet will temporarily be set to the result in the current iteration, and the resultSet of that iteration will replace the original result in the original resultSet. Basically it's a way to work with nested results and be able to send their results back. --foreEach=feature,value", array('loop', 'iterate', 'resultset')); # TODO This should probably move to a language module
				break;
			case 'singleLineMacro':
				$this->defineMacro($this->core->get('Global', $event), true);
				return $this->runMacro($this->lastCreatedMacro);
				break;
			case 'macro':
				$this->defineMacro($this->core->get('Global', $event));
				return $this->runMacro($this->lastCreatedMacro);
				break;
			case 'defineSingleLineMacro':
				$this->defineMacro($this->core->get('Global', $event), true);
				break;
			case 'defineMacro':
				$this->defineMacro($this->core->get('Global', $event));
				break;
			case 'runMacro':
				return $this->runMacro($this->core->get('Global', $event));
			case 'listMacros':
				return $this->listMacros();
				break;
			case 'loop':
				return $this->loopMacro($this->core->getResultSet(), $this->core->get('Global', $event));
			case 'forEach':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->doForEach($this->core->getResultSet(), $parms[0], $parms[1]);
			case 'followup':
				$this->loadSavedMacros();
				break;
			case 'last':
				break;
			default:
				return$this->runMacro($event);
				break;
		}
	}
	
	function defineMacro($macro, $useSemiColon=false, $macroName=false)
	{
		# Get macroName
		if (!$macroName)
		{
			$endOfName=strPos($macro, ':');
			$macroName=trim(substr($macro, 0, $endOfName));
			$actualMacro=trim(substr($macro, $endOfName+1));
		}
		else $actualMacro=$macro;
		$this->lastCreatedMacro=$macroName;
		
		$preCompile=array();
		
		if ($useSemiColon)
		{
			# Strip out new line characters and split into lines using ;
			$lines=explode(';', implode('', explode("\n", $actualMacro)));
		}
		else
		{
			# Split into lines usong \n
			$lines=explode("\n", $actualMacro);
		}
		
		# Precompile macro into a nested array of commands.
		$obj=null; # TODO check this. It may be needed in more places.
		$lineNumber=0;
		foreach ($lines as $line)
		{
			$lineNumber++;
			
			if (!trim($line)) continue;
			
			$endOfArgument=strPos($line, ' ');
			if ($endOfArgument)
			{
				# TODO The rtrim should be removed once I get past the current problem.
				$argument=substr($line, 0, $endOfArgument);
				$value=trim(substr($line, $endOfArgument+1));
			}
			else
			{
				$argument=$line;
				$value='';
			}
			
			
			switch ($argument)
			{
				case '#':
				case '':
					break;
				case '#onDefine':
					$parts=$this->core->splitOnceOn(' ', $value);
					$this->core->debug(3, "#onDefine {$parts[0]}={$parts[1]}");
					$this->core->callFeature($parts[0], $parts[1]);
					break;
				case '#onLoaded':
					$parts=$this->core->splitOnceOn(' ', $value);
					$this->core->debug(3, "#onLoaded {$parts[0]}={$parts[1]}");
					$this->core->callFeature("registerForEvent", "Macro,allLoaded,$parts[0],$parts[1]");
					break;
				default:
					//$this->core->addAction($argument, $value, $macroName);
					$preCompile[]=array(
						'argument'=>$argument,
						'value'=>$value,
						'nesting'=>array(),
						'macroName'=>$macroName,
						'lineNumber'=>$lineNumber
						);
					break;
			}
		}
		
		$this->compileFromArray($macroName, $preCompile);
	}
	
	function compileFromArray($macroName, $inputArray)
	{
		$outputArray=array();
		
		# Figure out nesting
		$lastRootKey=null;
		foreach($inputArray as $key=>$action)
		{
			if (substr($action['argument'], 0, 1) == '	')
			{
				if (!is_null($lastRootKey))
				{ // We have indentation. Remove 1 layer of indentation, and nest the argument.
					$this->core->debug(4, "compileFromArray($macroName:${action['lineNumber']}): Nested feature \"${action['argument']} ${action['value']}\"");
					$action['argument']=substr($action['argument'], 1);
					$outputArray[$lastRootKey]['nesting'][]=$action;
				}
				else
				{ // We have indentation, but no argument to nest it in. This is fatal.
					$this->core->debug(0, "compileFromArray($macroName:${action['lineNumber']}): Syntax error: Indentation without any features beforehand. The derived line was \"${action['argument']} ${action['value']}\"");
					# TODO implement atomic failure.
				}
			}
			else
			{
				$this->core->debug(4, "compileFromArray($macroName:${action['lineNumber']}): Root feature \"${action['argument']} ${action['value']}\"");
				$lastRootKey=$key;
				$outputArray[$lastRootKey]=$action;;
			}
		}
		
		# Compile
		foreach($outputArray as $key=>$action)
		{
			$obj=&$this->core->get('Features', $action['argument']);
			
			# Handle any nesting
			if (count($action['nesting']))
			{
				$subName="$macroName--{$action['lineNumber']}";
				
				$this->core->registerFeature($this, array($subName), $subName, "Derived macro for $macroName", "$macroName,hidden", true, 'NA');
				$outputArray[$key]['nesting']=$this->compileFromArray($subName, $action['nesting']);
				$this->core->addAction(trim($action['argument']), $action['value'].$subName, $macroName, $action['lineNumber']);
			}
			else
			{
				$this->core->addAction(trim($action['argument']), $action['value'], $macroName, $action['lineNumber']);
			}
		}
		
		return $outputArray;
	}
	
	function runMacro($macroName)
	{
		return $this->core->go($macroName);
	}
	
	function listMacros()
	{
		$store=$this->core->getStore();
		$output=array();
		if (!isset($store['Macros'])) return $output;
		foreach ($store['Macros'] as $macroName=>$macro)
		{
			$output[]=$macroName;
		}
		return $output;
	}
	
	function loopMacro($input, $paramaters)
	{
		$output=array();
		$firstComma=strpos($paramaters, ',');
		if ($firstComma!==false)
		{
			$macroName=substr($paramaters, 0, $firstComma);
			$macroParms=substr($paramaters, $firstComma+1);;
		}
		else
		{ // We haven't been passed any custom variables
			$macroName=$paramaters;
			$macroParms='';
		}
		
		if (!$macroName)
		{
			$this->core->complain($this, "No macro specified.");
			return false;
		}
		
		if (is_array($input))
		{
			foreach ($input as $key=>$in)
			{
				$this->core->debug(5, "loopMacro iterated for key $key");
				if (is_array($in)) $this->core->setCategoryModule('Result', $in);
				else
				{
					$this->core->setCategoryModule('Result', array());
					$this->core->set('Result', 'line', $in);
				}
				$this->core->set('Result', 'key', $key);
				
				$this->core->callFeature($macroName, $macroParms);
				$result=$this->core->getCategoryModule('Result');
				if (count($result)==1) $single=(isset($result['line']));
				else $single=false;
				
				if ($single)
				{
					$output[$key]=$result['line'];
				}
				else
				{
					if (count($result)) $output[$key]=$result;
					else $this->core->debug(4, "loopMacro: Skipped key \"$key\" since it looks like it has been unset.");
				}
			}
		}
		else $this->core->debug(5, "loopMacro: No input!");
		
		return $output;
	}
	
	function doForEach($data, $feature, $value)
	{
		$output=array();
		
		foreach ($data as $line)
		{
			if ($returnValue=$this->core->callFeatureWithDataset($feature, $value, $line))
			{
				$output[]=$returnValue;
			}
			else $output[]=$line;
		}
		
		return $output;
	}
	
	function loadSavedMacros()
	{
		# TODO This is repeated below. It should be done once.
		$profile=$this->core->get('General', 'profile');
		$fileList=$this->core->addItemsToAnArray('Core', 'macrosToLoad', $this->core->getFileList($this->core->get('General', 'configDir')."/profiles/$profile/macros"));
		
		# Pre-register all macros so that they can be nested without issue.
		foreach ($fileList as $fileName=>$fullPath)
		{
			if($fileName=='*') break;
			
			$nameParts=explode('.', $fileName);
			if ($nameParts[1]=='macro') // Only invest further time if it actually is a macro.
			{
				$macroName=$nameParts[0];
				$contents=file_get_contents($fullPath);
				$contentsParts=explode("\n", $contents);
				if (substr($contentsParts[0], 0, 2)=='# ')
				{
					$firstLine=substr($contentsParts[0], 2);
					$firstLineParts=explode('~', $firstLine);
					#$description=$firstLine;
					$description=$firstLineParts[0];
					$tags=(isset($firstLineParts[1]))?'macro,'.trim($firstLineParts[1]):'';
					$this->core->registerFeature($this, array($macroName), $macroName, $description, $tags, true, $fullPath);
				}
				else $this->core->complain($this, "$fullPath appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
			}
		}
		
		# Interpret and define all macros.
		foreach ($fileList as $fileName=>$fullPath)
		{
			if($fileName=='*') break;
			
			$nameParts=explode('.', $fileName);
			if ($nameParts[1]=='macro') // Only invest further time if it actually is a macro.
			{
				$macroName=$nameParts[0];
				$contents=file_get_contents($fullPath);
				$contentsParts=explode("\n", $contents);
				
				if (substr($contentsParts[0], 0, 2)=='# ')
				{
					$this->defineMacro($contents, false, $macroName);
				}
				else $this->core->complain($this, "$fullPath appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
			}
		}
		
		$this->core->callFeature('triggerEvent', 'Macro,allLoaded');
	}
}

$core=core::assert();
$core->registerModule(new Macro());
 
?>