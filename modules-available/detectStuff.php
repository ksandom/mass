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
				break;
			case 'followup':
				break;
			case 'last':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$core->registerModule(new DetectStuff());
 
?>