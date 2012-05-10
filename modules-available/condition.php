<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Adds the ability to put conditions into macros

class Condition extends Module
{
	function __construct()
	{
		parent::__construct('Example');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('notIfResult'), 'notIfResult', "Will run the specified command if we don't have a result from something that had previously run. Note that is different from and empty result. --notIfResult=\"command[ arguments]\" .", array('language'));
				$this->core->registerFeature($this, array('ifResult'), 'ifResult', '--ifNoResult="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('notIfEmptyResult'), 'notIfEmptyResult', '--notIfEmptyResult="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('ifEmptyResult'), 'ifEmptyResult', '--ifEmptyResult="command[ arguments]" .', array('language'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'ifNoResult':
				return $this->ifResult($this->core->getSharedMemory(), $this->core->get('Global', 'ifNoResult'), true);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function ifesult(&$input, $parms, $match=true)
	{
		if (($input) == $match)
		return $result;
	}
}

$core=core::assert();
$core->registerModule(new Condition());
 
?>