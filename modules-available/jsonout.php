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
				$this->core->registerFeature($this, array('j', 'json'), 'json', 'Send returned output as a json array.', array('json', 'output'));
				$this->core->registerFeature($this, array('toJson'), 'toJson', 'Put the results into a json array in the result array to be used with something like --singleStringNow.', array('json'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'toJson':
				return array(json_encode($this->core->getResultSet()));
				break;
			case 'json':
				$this->core->setRef('General', 'outputObject', $this);
				return $this->core->getResultSet(); # TODO Fix this!
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