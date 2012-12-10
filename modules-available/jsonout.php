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
				$this->core->registerFeature($this, array('resultJsonToArray'), 'resultJsonToArray', 'Convert json sitting in a result variable into an array. The default is to replace the existing field, but you can also specify a separate field to keep the original. --resultJsonToArray=fieldToConvert[,destinationField] .', array('json', 'array', 'resultSet'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'toJson':
				return array(json_encode($this->core->getResultSet()));
				break;
			case 'resultJsonToArray':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->resultJsonToArray($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'json':
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->setRef('General', 'echoObject', $this);
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
	
	function resultJsonToArray($input, $sourceField, $destinationField)
	{
		if (!$destinationField) $destinationField=$sourceField;
		$output=$input;
		
		foreach ($output as $key=>&$line)
		{
			if (is_string($line[$sourceField]))
			{
				$line[$destinationField]=json_decode($line[$sourceField], true);
			}
		}
		
		return $output;
	}
}

$core=core::assert();
$core->registerModule(new JsonOut());
 
?>