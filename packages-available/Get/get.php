<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Get stuff from external places



class GetThing extends Module
{
	private $connections=null;
	private $inputBuffers=null;
	
	function __construct()
	{
		parent::__construct('GetThing');
		
		$this->connections=array();
		$this->inputBuffers=array();
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('getHTTP'), 'getHTTP', "Get a specific URI for each host. --getHTTP=resultField,URI[,indexField[,timeout]] . eg --getHTTP=testResult,/status,IP,5 . indexField defaults to IP and timeout defaults to 5.", array('credentials'));
				break;
			case 'getHTTP':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 2);
				if ($parms) return $this->getThing($this->core->getResultSet(), $parms[0], $parms[2], $parms[1], $parms[3], 'http');
				break;
			case 'last':
				break;
			case 'followup':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function getThing($data, $resultField, $indexField, $uri, $timeout=5, $protocol='http')
	{
		// Open connections
		$output=array();
		$keyField='IP';
		$hostField='IP';
		
		if (!$indexField) $indexField=$hostField;
		if (!$timeout) $timeout=5;
		
		foreach ($data as $line)
		{
			if (!isset($line[$hostField]))
			{ 
				$this->core->debug(3, "Get->get: No IP set. Is this a really host record? Skipping this record.");
				continue;
			}
			
			if (!isset($line[$indexField]))
			{ 
				$this->core->debug(3, "Get->get: No $indexField set, which was requested to be used as the indexField. Skipping this record.");
				continue;
			}
			
			$url=$line[$hostField].$uri;
			$index=$line[$indexField];
			$this->openConnection($line[$hostField], $uri, $line[$indexField]);
			
			if(!isset($line[$keyField]))
			{
				echo "keyField=$keyField value={$line[$keyField]} indexField=$indexField index=$index\n\n";
				print_r($line);
				die("work out what's wrong here");
			}
			
			$output[$line[$keyField]]=$line;
			$output[$line[$keyField]][$resultField]='';
		}
		
		// Fetch data there are no open connections left, or we reach the timeout.
		$start=microtime(true);
		$timeoutNannoSeconds=$timeout*1000000;
		while (count($this->connections) and microtime(true)-$start<$timeoutNannoSeconds)
		{
			foreach ($data as $line)
			{
				$input=$this->getFromConnection($line[$indexField]);
				if (is_string($input)) $output[$line[$indexField]][$resultField].=$input;
				else
				{
					$this->core->debug(2, "getThing: Finished with {$line[$indexField]}. Closing now.");
					$this->closeConnection($line[$indexField]);
				}
			}
		}
		$this->core->debug(1, "getThing: Exited from all downloads.");
		
		// Close connections
		foreach ($data as $line)
		{
			$this->closeConnection($line[$indexField]);
			$this->core->debug(2, "getThing: Closing connection for {$line[$indexField]}");
		}
		
		$this->core->debug(1, "getThing: Finished.");
		
		return $output;
	}
	
	function openConnection($host, $uri, $index)
	{
		if ($this->connections[$index]=fopen("http://$host$uri","r"))
		{
			stream_set_blocking($this->connections[$index], 0);
		}
		else
		{
			unset($this->connections[$index]);
			$this->core->debug(3, "openConnection: Could not connect to $host$uri.");
		}
	}
	
	function getFromConnection($index)
	{
		if (isset($this->connections[$index]))
		{
			$input=fgets($this->connections[$index], 8192);
			if (is_string($input))
			{
				$this->core->debug(3, "getFromConnection: Got something from $index.");
				return $input;
			}
			else
			{
				$this->core->debug(3, "getFromConnection: Got something that wasn't a string (".gettype($input)."). Closing $index.");
				$this->closeConnection($index);
				return false;
			}
		}
		$this->core->debug(3, "getFromConnection: $index is not open.");
		return false;
	}
	
	function closeConnection($index)
	{
		if (isset($this->connections[$index]))
		{
			fclose($this->connections[$index]);
			unset($this->connections[$index]);
		}
	}
}

$core=core::assert();
$core->registerModule(new GetThing());
 
?>