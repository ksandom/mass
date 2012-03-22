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
				$this->core->registerFeature($this, array('macro'), 'macro', 'Define and run a macro. --macro=macroName:"command1=blah;command2=wheee"');
				$this->core->registerFeature($this, array('defineMacro'), 'defineMacro', 'Define a macro. --defineMacro=macroName:"command1=blah;command2=wheee"');
				$this->core->registerFeature($this, array('runMacro'), 'runMacro', 'Run a macro. --runMacro=macroName');
				$this->core->registerFeature($this, array('listMacros'), 'listMacros', 'List all macros');
				break;
			case 'macro':
				$this->defineMacro($this->core->get('Global', 'macro'));
				return $this->runMacro($this->lastCreatedMacro);
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
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function defineMacro($macro)
	{
		# Get macroName
		$endOfName=strPos($macro, ':');
		$macroName=trim(substr($macro, 0, $endOfName));
		$actualMacro=trim(substr($macro, $endOfName+1));
		$this->lastCreatedMacro=$macroName;
		
		# Strip out new line characters and split into new lines
		$lines=explode(';', implode('', explode("\n", $actualMacro)));
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
			
			$this->core->addAction($argument, $value, $macroName);
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
}

$core=core::assert();
$core->registerModule(new Macro());
 
?>