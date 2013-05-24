<?php
# Copyright (c) 2013 Kevin Sandom under the BSD License. See LICENSE for full details.

class Base extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Base');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('intToHex'), 'intToHex', 'Take an int and put the output into a variable. --intToHex=Category,variableName,intValue', array('array', 'hex', 'int'));
				$this->core->registerFeature($this, array('hexToInt'), 'hexToInt', 'Take a hex value and put the output an int into a variable. --hexToInt=Category,variableName,hexValue', array('array', 'hex', 'int'));
				$this->core->registerFeature($this, array('hexToInts'), 'hexToInts', 'Take a hex input, split it into multiple parts and put each output an int into a variable array. --hexToInts=Category,variableName,hexValue[,hexWidth] . hexWidth specifies how many characters to take per hex input. The default is 2 characters, which represents an 8 bit byte. This is useful for taking a hex RGB value, eg for 33CCFF the input is 33, CC and FF. The resulting values will be stored in Category,variableName,0 , Category,variableName,1 and Category,variableName,2 .', array('array', 'hex', 'int'));
				break;
			case 'followup':
				break;
			case 'last':
				break;

			case 'intToHex':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 3, $event, $originalParms, $parms))
				{
					# TODO find out what this function is
					$this->core->set($parms[0], $parms[1], dechex($parms[2]));
				}
				break;
			case 'hexToInt':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 3, $event, $originalParms, $parms))
				{
					# TODO find out what this function is
					$this->core->set($parms[0], $parms[1], hexdec($parms[2]));
				}
				break;
			case 'hexToInts':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 4, true);
				# TODO find out what this function is
				$this->core->set($this->hexToInts($parms[0], $parms[1], $parms[2], $parms[3]));
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function hexToInts($category, $value, $input, $hexWidth)
	{
		$numberOfParts=strlen($value)/$hexWidth;
		
		for ($partNumber=0;$partNumber<$numberOfParts;$partNumber++)
		{
			$part=substr($value, $partNumber*$hexWidth, $hexWidth);
			
			# TODO find out what this function is
			$this->core->setNested($category, $value, array($partNumber, hexdec($part)));
		}
	}
}

$core=core::assert();
$core->registerModule(new Base());



?>