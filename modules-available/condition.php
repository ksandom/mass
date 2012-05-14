<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Adds the ability to put conditions into macros

/*
	There are essentially two variants with one alias and each having their not equivilent
		* ifResultExists
		* ifNotEmptyResult ifResult <-- Most of the time you'll want this one

*/

class Condition extends Module
{
	function __construct()
	{
		parent::__construct('Condition');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('notIfResultExists'), 'notIfResultExists', "Will run the specified command if we don't have a result from something that had previously run. Note that is different from and empty result. --notIfResultExists=\"command[ arguments]\" .", array('language'));
				$this->core->registerFeature($this, array('ifResultExists'), 'ifResultExists', '--ifResultExists="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('ifResult', 'notIfEmptyResult'), 'notIfEmptyResult', '--notIfEmptyResult="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('notIfResult', 'ifEmptyResult'), 'ifEmptyResult', '--ifEmptyResult="command[ arguments]" .', array('language'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'notIfResultExists':
				return $this->ifResultExists($this->core->getSharedMemory(), $this->core->get('Global', 'notIfResultExists'), false);
				break;
			case 'ifResultExists':
				return $this->ifResultExists($this->core->getSharedMemory(), $this->core->get('Global', 'ifResultExists'), true);
				break;
			case 'notIfEmptyResult':
				return $this->ifNotEmptyResult($this->core->getSharedMemory(), $this->core->get('Global', 'notIfEmptyResult'), true);
				break;
			case 'ifEmptyResult':
				return $this->ifNotEmptyResult($this->core->getSharedMemory(), $this->core->get('Global', 'ifResult'), false);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function ifResultExists(&$input, $parms, $match=true)
	{
		if ((is_array($input)) == $match)
		{
			$this->takeAction($input, $parms);
		}
		else return false;
	}

	function ifNotEmptyResult(&$input, $parms, $match=true)
	{
		if ((is_array($input) and count($input)) == $match)
		{
			# TODO There is an error in the above logic with the below logic. Above may work, but the below needs to be appropriate to the input...
			$keys=array_keys($input);
			if ($input[$keys[0]])
			{
				$this->takeAction($input, $parms);
			}
		}
		else return false;
	}
	
	function takeAction(&$input, $parms)
	{
		# TODO Kevin: Do this. It should trigger an event on the core.
		$parmParts=$this->core->splitOnceOn(' ', $parms);
		return $this->core->triggerEvent($parmParts[0], $parmParts[1]);
	}
}

$core=core::assert();
$core->registerModule(new Condition());
 
?>