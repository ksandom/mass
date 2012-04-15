<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Covert to and work with strings

class String extends Module
{
	function __construct()
	{
		parent::__construct('String');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				// This isn't ready for usage yet.
				$this->core->registerFeature($this, array('s', 'singleString'), 'singleString', 'Send returned output as one large string. Each entry will be separated by a new line.');
				$this->core->registerFeature($this, array('stringToFile'), 'string', 'Send returned output as a string to a file at the end of the processing. Each entry will be separated by a new line.');
				$this->core->registerFeature($this, array('stringToFileNow'), 'string', 'Send returned output as a string. Each entry will be separated by a new line.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'singleString':
				$this->core->setRef('General', 'outputObject', $this);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function out($output)
	{
		$readyValue=(is_array($output))?implode("\n", $output):$output;
		echo $readyValue;
	}
}

$core=core::assert();
$core->registerModule(new String());
 
?>