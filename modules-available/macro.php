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
				break;
			case 'singleLineMacro':
				$this->defineMacro($this->core->get('Global', 'macro'), true);
				return $this->runMacro($this->lastCreatedMacro);
				break;
			case 'macro':
				$this->defineMacro($this->core->get('Global', 'macro'));
				return $this->runMacro($this->lastCreatedMacro);
				break;
			case 'defineSingleLineMacro':
				$this->defineMacro($this->core->get('Global', 'defineMacro'), true);
				break;
			case 'defineMacro':
				$this->defineMacro($this->core->get('Global', 'defineMacro'));
				break;
			case 'runMacro':
				return $this->runMacro($this->core->get('Global', 'runMacro'));
			case 'listMacros':
				return $this->listMacros();
				break;
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
	
	function defineMacro($macro, $useSemiColon=false)
	{
		# Get macroName
		$endOfName=strPos($macro, ':');
		$macroName=trim(substr($macro, 0, $endOfName));
		$actualMacro=trim(substr($macro, $endOfName+1));
		$this->lastCreatedMacro=$macroName;
		
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
		
		foreach ($lines as $line)
		{
			$trimmedLine=trim($line);
			$endOfArgument=strPos($trimmedLine, ' ');
			if ($endOfArgument)
			{
				$argument=trim(substr($line, 0, $endOfArgument));
				$value=trim(substr($line, $endOfArgument+1));
			}
			else
			{
				$argument=$trimmedLine;
				$value='';
			}
			
			if ($argument and $argument!='#') $this->core->addAction($argument, $value, $macroName);
		}
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
	
	function loadSavedMacros()
	{
		# TODO This is repeated below. It should be done once.
		$fileList=$this->core->getFileList($this->core->get('General', 'configDir').'/macros-enabled');
		
		# Pre-register all macros so that they can be nested without issue.
		foreach ($fileList as $fileName=>$fullPath)
		{
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
					$this->core->registerFeature($this, array($macroName), $macroName, 'Macro: '.$description, $tags);
				}
				else $this->core->complain($this, "$fullPath appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
			}
		}
		
		# Interpret and define all macros.
		foreach ($fileList as $fileName=>$fullPath)
		{
			$nameParts=explode('.', $fileName);
			if ($nameParts[1]=='macro') // Only invest further time if it actually is a macro.
			{
				$macroName=$nameParts[0];
				$contents=file_get_contents($fullPath);
				$contentsParts=explode("\n", $contents);
				
				if (substr($contentsParts[0], 0, 2)=='# ')
				{
					$this->defineMacro("$macroName:$contents", false);
				}
				else $this->core->complain($this, "$fullPath appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
			}
		}
	}
}

$core=core::assert();
$core->registerModule(new Macro());
 
?>