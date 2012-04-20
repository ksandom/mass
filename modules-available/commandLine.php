<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class CommandLine extends Module
{
	function __construct()
	{
		parent::__construct('CommandLine');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('h', 'help'), 'help', 'Display this help.', array('user'));
				$this->core->registerFeature($this, array('printr'), 'printr', 'Print output using the print_r() function. Particularly useful for debugging.');
				
				$this->core->setRef('General', 'outputObject', $this);
				break;
			case 'followup':
				break;
			case 'last':
				$this->processArgs();
				break;
			case 'help':
				$this->showHelp();
				break;
			case 'printr':
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->set('General', 'outputStyle', 'printr');
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function processArgs()
	{
		$arg=&$this->core->get('CommandLine', 'arguments');
		#print_r($arg);
		$max=count($arg);
		$possibleFlagsRemaining=true;
		
		for ($i=1;$i<$max;$i++) # NOTE Chosen for instead of foreach so we can nicely grab/skip the next item while maintaining position
		{
			$length=strlen($arg[$i]);
			if ($arg[$i][0]=='-' and $possibleFlagsRemaining)
			{ # The argument begins with - or --
				if ($arg[$i]=='-')
				{ // illegal
					die ("Found a stray '-'. Perhaps you meant '--' ?\n");
				}
				elseif ($arg[$i]=='--')
				{ // End of flags
					$possibleFlagsRemaining=false;
				}
				elseif ($arg[$i][1]=='-')
				{ // Double dash parameter
					if (strpos($arg[$i], '='))
					{
						$equalsPos=strpos($arg[$i], '=');
						$argument=substr($arg[$i], 2, $equalsPos-2);
						$value=substr($arg[$i], $equalsPos+1);
						$this->core->set('Global', $argument, $value);
						$this->core->addAction($argument, $value);
					}
					else
					{
						$argument=substr($arg[$i], 2);
						$this->core->addAction($argument);
					}
					
					# take action on argument
					//$this->setAction($argument);
				}
				else
				{ // Single dash parameter
					# take each parm
					$singleMax=strlen($arg[$i]);
					for ($char=1;$char<$singleMax;$char++)
					{
						# take action
						$single=substr($arg[$i], $char, 1);
						//$this->setAction($single);
						$this->core->addAction($single);
					}
				}
			}
		}
	}
	
	function setAction($argument)
	{
		$obj=&$this->core->get('Features', $argument);
		if (is_array($obj)) $this->core->setRef('Actions', $argument, $obj);
		else
		{
			echo "Could not find a module to match '$argument'\n";
		}
	}
	
	function showHelp()
	{
		$store=$this->core->getStore();
		$track=array();
		
		$programName=$this->core->get('General', 'programName');
		$description=$this->core->get('General', 'description');
		echo "Help\n----\n$programName: $description\n\n";
		foreach ($store['Features'] as $name=>$details)
		{
			if (!isset($track[$details['flags'][0]]))
			{
				$track[$details['flags'][0]]=true; # Make sure aliases don't cause us to display the same help twice.
				$visualFlags=array();
				foreach ($details['flags'] as $flag)
				{
					$visualFlags[]=(strlen($flag)==1)?"-$flag":"--$flag";
				}
				
				$finalVisualFlags=implode(', ', $visualFlags);
				$objName=$details['obj']->getName();
				echo "$objName: $finalVisualFlags => {$details['description']}\n";
			}
		}
	}
	
	function out($output, $indent='', $prefix=false)
	{
		if ($this->core->get('General', 'outputStyle')=='printr')
		{
			print_r($output);
		}
		else
		{
			if (is_string($output)) 
			{
				$derivedPrefix=($prefix or is_numeric($prefix))?"$prefix: ":'';
				echo "$indent$derivedPrefix$output\n";
			}
			elseif (is_array($output))
			{
				$derivedPrefix=($prefix!==false)?"$prefix: ":'';
				echo "$indent$derivedPrefix\n";
				foreach ($output as $key=>$value)
				{
					$this->out($value, $indent.'  ', "$key");
				}
			}
			elseif (is_null($output))
			{
				if ($prefix)
				{
					$derivedPrefix=($prefix!==false)?"$prefix: ":'';
					echo "$indent{$derivedPrefix}NULL\n";
				}
			}
			else
			{
				echo "$indent{$prefix}: I can't display this data type yet.\n";
			}
		}
	}
}

$core=core::assert();
$core->registerModule(new CommandLine());
 
?>