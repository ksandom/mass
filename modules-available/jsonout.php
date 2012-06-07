<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class JsonOut extends Module
{
	function __construct()
	{
		parent::__construct('JsonOut');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
<<<<<<< HEAD
				$this->core->registerFeature($this, array('j', 'json'), 'json', 'Send returned output as a json array.');
				$this->core->registerFeature($this, array('toJson'), 'toJson', 'Put the results into a json array in the result array to be used with something like --singleStringNow.');
=======
				$this->core->registerFeature($this, array('j', 'json'), 'json', 'Send returned output as a json array.', array('json'));
				$this->core->registerFeature($this, array('toJson'), 'toJson', 'Put the results into a json array in the result array to be used with something like --singleStringNow.', array('json'));
>>>>>>> kevdev
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'toJson':
				return array(json_encode($this->core->getSharedMemory()));
				break;
			case 'json':
				$this->core->setRef('General', 'outputObject', $this);
				return $this->core->getSharedMemory(); # TODO Fix this!
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function out($output)
	{
		$readyValue=(is_array($output))?$output:array($output);
		echo json_encode($readyValue);
	}
}

$core=core::assert();
$core->registerModule(new JsonOut());
 
?>