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
				# TODO Think of a better way to do this, as it's not needed for the web interface.
				$this->core->registerFeature($this, array('oldHelp'), 'oldHelp', 'Place holder', array('placeholder'));
				$this->core->registerFeature($this, array('searchHelp'), 'searchHelp', 'Place holder', array('placeholder'));
				$this->core->registerFeature($this, array('getTags'), 'getTags', 'Place holder', array('placeholder'));
				$this->core->registerFeature($this, array('printr', 'print_r'), 'printr', 'Place holder', array('placeholder'));

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