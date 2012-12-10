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
		$arg=&$this->core->get('BasicWeb', 'arguments');
		$this->core->addAction('json', '');
		foreach ($arg as $argument=>$value)
		{
			$this->core->addAction($argument, $value);
		}
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
				$this->core->echoOut("$indent{$this->codes['green']}$derivedPrefix$output");
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
					$this->core->echoOut("$indent{$this->codes['purple']}{$derivedPrefix}NULL");
				}
			}
			elseif (is_numeric($output))
			{
				$this->core->echoOut("$indent{$this->codes['purple']}{$derivedPrefix}$output");
			}
			elseif (is_bool($output))
			{
				$display=($output)?'True':'False';
				$this->core->echoOut("$indent{$this->codes['purple']}{$derivedPrefix}$display");
			}
			else
			{
				$this->core->echoOut("$indent{$this->codes['red']}{$prefix}{$this->codes['default']}: {$this->codes['brightBlack']}I can't display this data type yet.{$this->codes['default']}");
			}
			
			$this->core->echoOut("{$this->codes['default']}");
		}
	}
}

$core=core::assert();
$core->registerModule(new BasicWeb());
 
?>