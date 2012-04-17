<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Covert to and work with strings

class String extends Module
{
	private $outputFile=false;
	
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
				$this->core->registerFeature($this, array('s', 'singleString'), 'singleString', 'Set final output to send the returned output as one large string. Each entry will be separated by a new line.');
				$this->core->registerFeature($this, array('stringToFile'), 'stringToFile', 'Send returned output as a string to a file at the end of the processing. Each entry will be separated by a new line. --stringToFile=filename');
				$this->core->registerFeature($this, array('singleStringNow'), 'singleStringNow', 'Send returned output as a string. Each entry will be separated by a new line. --singleStringNow[=filename] . If filename is omitted, stdout will be used instead.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'singleString':
				$this->stringToFile();
				break;
			case 'stringToFile':
				$this->stringToFile($this->core->get('Global', 'stringToFile'));
				break;
			case 'singleStringNow':
				$this->singleStringNow($this->core->get('Global', 'singleStringNow'), $this->core->getSharedMemory());
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function stringToFile($filename=false)
	{
		# perfom checks
		#if ($filename!==false) # TODO We could check for bad paths
		
		# set filename
		$this->outputFile=$filename;
		
		# set output type
		$this->core->setRef('General', 'outputObject', $this);
	}
	
	function singleStringNow($filename, $output)
	{
		$readyValue=(is_array($output))?implode("\n", $output):$output;
		if ($filename) file_put_contents($filename, $readyValue);
		else echo $readyValue;
	}
	
	function out($output)
	{
		$this->singleStringNow($this->outputFile, $outputl);
	}
}

$core=core::assert();
$core->registerModule(new String());
 
?>