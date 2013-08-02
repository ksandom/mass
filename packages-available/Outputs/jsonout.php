<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class JsonOut extends Module
{
	private $forceObject=true;
	
	function __construct()
	{
		parent::__construct('JsonOut');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('j', 'json', 'jsonObjects'), 'json', 'Send returned output as a json object list.', array('json', 'output'));
				$this->core->registerFeature($this, array('jsonArray'), 'jsonArray', 'Send returned output as a json array. NOTE that this is suboptimal since the keys have to be iteratively stripped. If you know a better way, please get in touch via github.', array('json', 'output'));
				$this->core->registerFeature($this, array('toJson', 'toJsonObjects'), 'toJson', 'Put the results into a json object list in the result array to be used with something like --singleStringNow.', array('json'));
				$this->core->registerFeature($this, array('toJsonArray'), 'toJsonArray', 'Put the results into a json array in the result array to be used with something like --singleStringNow. NOTE that this is suboptimal since the keys have to be iteratively stripped. If you know a better way, please get in touch via github.', array('json'));
				$this->core->registerFeature($this, array('resultJsonToArray'), 'resultJsonToArray', 'Convert json sitting in a result variable into an array. The default is to replace the existing field, but you can also specify a separate field to keep the original. --resultJsonToArray=fieldToConvert[,destinationField] .', array('json', 'array', 'resultSet'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'toJson':
				return array(json_encode($this->core->getResultSet(), JSON_FORCE_OBJECT));
				break;
			case 'toJsonArray':
				return array(json_encode($this->stripKeys($this->core->getResultSet())));
				break;
			case 'resultJsonToArray':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->resultJsonToArray($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'json':
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->setRef('General', 'echoObject', $this);
				$this->forceObject=true;
				break;
			case 'jsonArray':
				$this->core->setRef('General', 'outputObject', $this);
				$this->core->setRef('General', 'echoObject', $this);
				$this->forceObject=false;
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function out($output)
	{
		$readyValue=(is_array($output))?$output:array($output);
		if ($this->forceObject)
		{
			echo json_encode($readyValue, JSON_FORCE_OBJECT);
		}
		else
		{
			echo json_encode($this->stripKeys($readyValue));
		}
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
	
	function stripKeys($input)
	{
		$output=array();
		foreach ($input as $line) $output[]=$line;
		
		return $output;
	}
}

$core=core::assert();
$core->registerModule(new JsonOut());
 
?>