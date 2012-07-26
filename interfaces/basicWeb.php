<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class BasicWeb extends Module
{
	private $track=null;
	private $store=null;
	private $codes=false;
	
	function __construct()
	{
		parent::__construct('BasicWeb');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				break;
			case 'followup':
				break;
			case 'last':
				$this->core->callFeature('json', '');
				$this->processArgs();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function processArgs()
	{
		# TODO Make this work for BasicWeb
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
			echo "Could not find a module to match '$argument'\n";
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
				echo "$indent{$this->codes['green']}$derivedPrefix$output\n";
			}
			elseif (is_array($output))
			{
				echo "$indent{$this->codes['cyan']}$derivedPrefix\n";
				foreach ($output as $key=>$value)
				{
					$this->out($value, $indent.'  ', "$key");
				}
			}
			elseif (is_null($output))
			{
				if ($prefix)
				{
					echo "$indent{$this->codes['purple']}{$derivedPrefix}NULL\n";
				}
			}
			elseif (is_numeric($output))
			{
				echo "$indent{$this->codes['purple']}{$derivedPrefix}$output\n";
			}
			elseif (is_bool($output))
			{
				$display=($output)?'True':'False';
				echo "$indent{$this->codes['purple']}{$derivedPrefix}$display\n";
			}
			else
			{
				echo "$indent{$this->codes['red']}{$prefix}{$this->codes['default']}: {$this->codes['brightBlack']}I can't display this data type yet.{$this->codes['default']}\n";
			}
			
			echo "{$this->codes['default']}";
		}
	}
}

$core=core::assert();
$core->registerModule(new BasicWeb());
 
?>