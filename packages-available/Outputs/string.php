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
				$this->core->registerFeature($this, array('s', 'singleString'), 'singleString', 'Set final output to send the returned output as one large string. Each entry will be separated by a new line.', array('string'));
				$this->core->registerFeature($this, array('stringToFile'), 'stringToFile', 'Send returned output as a string to a file at the end of the processing. Each entry will be separated by a new line. --stringToFile=filename', array('string'));
				$this->core->registerFeature($this, array('singleStringNow'), 'singleStringNow', 'Send returned output as a string. Each entry will be separated by a new line. --singleStringNow[=filename] . If filename is omitted, stdout will be used instead.', array('string'));
				$this->core->registerFeature($this, array('getSingleString'), 'getSingleString', 'Return a single string containing all the results.', array('string'));
				$this->core->registerFeature($this, array('getSingleStringUsingSeparator'), 'getSingleStringUsingSeparator', 'Return a single string containing all the results with a custom separator --getSingleStringUsingSeparator=separator .', array('string'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'singleString':
				$this->stringToFile();
				break;
			case 'stringToFile':
				$this->stringToFile($this->core->get('Global', $event));
				break;
			case 'singleStringNow':
				$output=$this->singleStringNow($this->core->get('Global', $event), $this->core->getResultSet());;
				if (is_array($output))$output=implode(',', $output); # TODO decide if this is the best way to output it
				echo $output;
				break;
			case 'getSingleString':
				return $this->singleStringNow(false, $this->core->getResultSet());
				break;
			case 'getSingleStringUsingSeparator':
				return $this->singleStringNow(false, $this->core->getResultSet(), $this->core->get('Global', $event));
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
	
	function singleStringNow($filename, $output, $separator="\n")
	{
		$readyValue=(is_array($output))?implode($separator, $output)."\n":$output;
		if ($filename) 
		{
			$this->core->debug(3, "singleStringNow: Sending to $filename");
			file_put_contents($filename, $readyValue);
		}
		else
		{
			$this->core->debug(3, "singleStringNow: Returning value of length ".strlen($readyValue));
			return array($readyValue);
		}
	}
	
	function out($output)
	{
		$this->core->debug(4, "String: Writing output to {$this->outputFile}");
		$result=$this->singleStringNow($this->outputFile, $output);
		echo $result[0];
	}
}

$core=core::assert();
$core->registerModule(new String());
 
?>