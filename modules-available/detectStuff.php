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
				$input
				$this->detect();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function detect($moduleName, $seed)
	{
	}
}

$core=core::assert();
$core->registerModule(new DetectStuff());
 
?>