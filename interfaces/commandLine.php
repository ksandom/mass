<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class CommandLine extends Module
{
	private $track=null;
	private $store=null;
	private $codes=false;
	
	function __construct()
	{
		parent::__construct('CommandLine');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('printr', 'print_r'), 'printr', 'Print output using the print_r() function. Particularly useful for debugging.', array('debug', 'dev', 'output'));
				$this->core->registerFeature($this, array('nested'), 'nested', 'Print output using a simple nested format. Particularly useful for debugging.', array('debug', 'dev', 'output'));
				
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->setRef('General', 'echoObject', $this);
				break;
			case 'followup':
				break;
			case 'last':
				$this->core->callFeature('triggerEvent', 'CommandLine,startup');
				$this->processArgs();
				break;
			case 'printr':
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->set('General', 'outputStyle', 'printr');
				break;
			case 'nested':
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->set('General', 'outputStyle', 'nested');
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
		$stray=array();
		
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
			else
			{
				$stray[]=$arg[$i];
			}
		}
		
		$this->core->set('Global', 'stray', implode(' ', $stray));
	}
	
	function setAction($argument)
	{
		$obj=&$this->core->get('Features', $argument);
		if (is_array($obj)) $this->core->setRef('Actions', $argument, $obj);
		else
		{
			$this->core->debug(0,"Could not find a module to match '$argument'");
		}
	}
	
	function assertCodes()
	{
		if (!$this->codes)
		{
			$this->codes=$this->core->getCategoryModule('Color');
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
			$this->assertCodes();
			
			$derivedPrefix=($prefix or is_numeric($prefix))?"$prefix{$this->codes['default']}: ":'';
			if (is_string($output)) 
			{
				$this->core->echoOut("$indent{$this->codes['green']}$derivedPrefix$output{$this->codes['default']}");
			}
			elseif (is_array($output))
			{
				$this->core->echoOut("$indent{$this->codes['cyan']}$derivedPrefix");
				foreach ($output as $key=>$value)
				{
					$this->out($value, $indent.'  ', "$key");
				}
			}
			elseif (is_null($output))
			{
				if ($prefix)
				{
					$this->core->echoOut("$indent{$this->codes['purple']}{$derivedPrefix}NULL{$this->codes['default']}");
				}
			}
			elseif (is_numeric($output))
			{
				$this->core->echoOut("$indent{$this->codes['purple']}{$derivedPrefix}$output{$this->codes['default']}");
			}
			elseif (is_bool($output))
			{
				$display=($output)?'True':'False';
				$this->core->echoOut("$indent{$this->codes['purple']}{$derivedPrefix}$display{$this->codes['default']}");
			}
			else
			{
				$this->core->echoOut("$indent{$this->codes['red']}{$prefix}{$this->codes['default']}: {$this->codes['brightBlack']}I can't display this data type yet.{$this->codes['default']}");
			}
		}
	}
	
	function put($output)
	{
		foreach ($output as $line) echo "$line\n";
	}
}

$core=core::assert();
$core->registerModule(new CommandLine());
 
?>