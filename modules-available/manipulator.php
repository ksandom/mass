<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manipulate output

class Manipulator extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Manipulator');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('toString'), 'toString', 'Convert array or arrays into an array of strings.');
				break;
				$this->core->registerFeature($this, array('f', 'flatten'), 'flatten', 'Flatten an array or arrays into a keyed array of values.');
				break;
			case 'toString':
				break;
			case 'flatten':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
}

$core=core::assert();
$core->registerModule(new Manipulator());
 
?>