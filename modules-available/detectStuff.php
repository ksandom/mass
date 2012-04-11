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
				$this->core->registerFeature($this, array('detect'), 'detectGUITerminal', 'Detect something based on a seed. --detectGUITerminal=ModuleName'.valueSeparator.'seedVariable . See docs/detect.md for more details.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'detect':
				$parms=$this->core->interpretParms($this->get('Global', 'detect'));
				$input=$this->core->get($parms[0], $parms[1]);
				$seed=$this->core->interpretParms($input);
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
	}
	
	function testSeedItem($moduleName, $seedItem, $itemsToGet)
	{
		# If we don't know it. Get out early.
		if (!$this->core->get($moduleName, $seedItem)) return false;
		
		$test=$this->core->get($moduleName, $seedItem);
		if ()
		{
			
		}
		
		$items=array();
		foreach($itemsToGet as $itemName)
		{
			
		}
	}
}

$core=core::assert();
$core->registerModule(new DetectStuff());
 
?>