<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Get stuff from external places



class Get extends Module
{
	private $connections=null;
	private $inputBuffers=null;
	
	function __construct()
	{
		parent::__construct('AWS');
		
		$this->connections=array();
		$this->inputBuffers=array();
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				#$this->core->registerFeature($this, array('AWSSetCred'), 'AWSSetCred', "Set the AWS credentials. --AWSSetCred=awsKey,awsSecret", array('credentials'));
			case 'last':
				break;
			case 'followup':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function get($data, $indexField, $uri)
	{
		// Open connections
		foreach ($data as $line)
		{
			if (!isset($data['IP']))
			{ 
				$this->core->debug(3, "Get->get: No IP set. Is this a really host record? Skipping this record.");
				continue;
			}
			
			if (!isset($data[$indexField]))
			{ 
				$this->core->debug(3, "Get->get: No $indexField set, which was requested to be used as the indexField. Skipping this record.");
				continue;
			}
			
			$url=$data['IP'].$uri;
			$index=$data[$indexField];
			$this->openConnection($data['IP'], $index;)
		}
		
		// Fetch data
		
		// Close connections
	}
	
	function openConnection($url, $index)
	{
		
	}
	
	function getFromConnection($index)
	{
	}
}

$core=core::assert();
$core->registerModule(new Get());
 
?>