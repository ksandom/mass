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
				$this->core->registerFeature($this, array('detect'), 'detect', 'Detect something based on a seed. --detect=ModuleName'.valueSeparator.'seedVariable . See docs/detect.md for more details.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'detect':
				$parms=$this->core->interpretParms($this->core->get('Global', 'detect'));
				$input=$this->core->get($parms[0], $parms[1]);
				$seed=explode(':', $input);
				$this->detect($parms[0], $seed);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function detect($moduleName, $seed)
	{
		$itemsToGet=array('Name', 'Description', 'Cmd', 'Parms');
		
		foreach ($seed as $seedItem)
		{
			if ($this->testSeedItem($moduleName, $seedItem, $itemsToGet)) break;
		}
		
		$this->core->set($moduleName, 'run', $this->core->now());
	}
	
	function testSeedItem($moduleName, $seedItem, $itemsToGet)
	{
		# If we don't know it. Get out early.
		if (!$this->core->get($moduleName, $seedItem.'Name')) return false;
		
		$test=$this->core->get($moduleName, $seedItem."Test");
		$testResult=`$test`;
		if ($testResult)
		{ // If the test passes, copy the items across.
			$groupName=$this->core->get($moduleName, $seedItem."Group");
			foreach($itemsToGet as $itemName)
			{
				$item=$this->core->get($moduleName, $seedItem.$itemName);
				$this->core->set($moduleName, $groupName.$itemName, $item);
			}
			
			$this->core->set($moduleName, $groupName.'TestResult', trim($testResult));
			return true;
		}
		return false;
	}
}

$core=core::assert();
$core->registerModule(new DetectStuff());
 
?>