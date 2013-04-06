<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Detect stuff

class DetectStuff extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('DetectStuff');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('detect'), 'detect', 'Detect something based on a seed. --detect=Category'.valueSeparator.'seedVariable,'.valueSeparator.'destinationGroup . See docs/detect.md for more details.');
				$this->core->registerFeature($this, array('getHostName'), 'getHostName', 'Save the hostname to Local,hostName .', array('hostname'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'detect':
				$parms=$this->core->interpretParms($this->core->get('Global', 'detect'));
				$input=$this->core->get($parms[0], $parms[1]);
				$seed=explode(':', $input);
				$this->detect($parms[0], $seed, $parms[2]);
				break;
			case 'getHostName':
				$this->core->set('Local', 'hostName', gethostname());
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function detect($category, $seed, $group)
	{
		$itemsToGet=array('Name', 'Description', 'CMD');
		
		foreach ($seed as $seedItem)
		{
			if ($this->testSeedItem($category, $seedItem, $itemsToGet, $group)) break;
		}
		
		$this->core->set($category, 'run', $this->core->now());
	}
	
	function testSeedItem($category, $seedItem, $itemsToGet, $group)
	{
		# If we don't know it. Get out early.
		if (!$this->core->get($category, $seedItem.'Name')) return false;
		
		$test=$this->core->get($category, $seedItem."Test");
		$testResult=file_exists($test);
		#echo "$seedItem: '$test'\n";
		if (!$testResult) $testResult=`$test 2>/dev/null`;
		
		if ($testResult)
		{ // If the test passes, copy the items across.
			#echo "Successed using $category,$seedItem. group=$group \n";
			foreach($itemsToGet as $itemName)
			{
				$item=$this->core->get($category, $seedItem.$itemName);
				$this->core->set($category, $group.$itemName, $item);
			}
			
			$this->core->set($category, $group.'TestResult', trim($testResult));
			return true;
		}
		return false;
	}
}

$core=core::assert();
$core->registerModule(new DetectStuff());
 
?>