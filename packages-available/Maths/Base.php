<?php
# Copyright (c) 2013 Kevin Sandom under the BSD License. See LICENSE for full details.

define('defaultHexWidth', 2);

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
				$this->core->registerFeature($this, array('intToHex'), 'intToHex', 'Take an int and put the output into a variable. --intToHex=Category,variableName,intValue[,hexWidth]', array('array', 'hex', 'int'));
				$this->core->registerFeature($this, array('hexToInt'), 'hexToInt', 'Take a hex value and put the output an int into a variable. --hexToInt=Category,variableName,hexValue', array('array', 'hex', 'int'));
				$this->core->registerFeature($this, array('hexToInts'), 'hexToInts', 'Take a hex input, split it into multiple parts and put each output an int into a variable array. --hexToInts=Category,variableName,[hexWidth],hexValue . hexWidth specifies how many characters to take per hex input. The default is '.defaultHexWidth.' characters, which represents an 8 bit byte. This is useful for taking a hex RGB value, eg for 33CCFF the input is 33, CC and FF. The resulting values will be stored in Category,variableName,0 , Category,variableName,1 and Category,variableName,2 .', array('array', 'hex', 'int'));
				$this->core->registerFeature($this, array('intsToHex'), 'intsToHex', 'Take a series of integers as input and make them one hex output. This is useful for taking a individual RGB values and producing a single output for HTML/CSS. --intsToHex=Category,saveToName,hexWidth,intValue1[,intValue2[,intValue3[,intValue4[,etc]]]]', array('array', 'hex', 'int'));
				break;
			case 'followup':
				break;
			case 'last':
				break;

			case 'intToHex':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 3, $event, $originalParms, $parms))
				{
					$this->core->set($parms[0], $parms[1], $this->intToHex($parms[2], $parms[3]));
				}
				break;
			case 'hexToInt':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 3, $event, $originalParms, $parms))
				{
					$this->core->set($parms[0], $parms[1], hexdec($parms[2]));
				}
				break;
			case 'hexToInts':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 4, true);
				$this->hexToInts($parms[0], $parms[1], $parms[3], $parms[2]);
				break;
			case 'intsToHex':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3, false);
				$this->core->set($parms[0], $parms[1], $this->intsToHex($parms[2], $parms[3]));
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function intToHex($value, $hexWidth)
	{
		if (!$hexWidth) $hexWidth=defaultHexWidth;
		
		$hexValue=dechex($value);
		$hexCharacterLength=strlen($hexValue);
		if ($hexCharacterLength<$hexWidth)
		{
			$hexValue=str_pad($hexValue, $hexWidth, '0', STR_PAD_LEFT);
		}
		
		return strtoupper($hexValue);
	}
	
	function hexToInts($category, $value, $input, $hexWidth)
	{
		if (!$hexWidth) $hexWidth=defaultHexWidth;
		$numberOfParts=strlen($value)/$hexWidth;
		
		for ($partNumber=0;$partNumber<$numberOfParts-1;$partNumber++)
		{
			$part=substr($input, $partNumber*$hexWidth, $hexWidth);
			
			$this->core->debug(3, "hexToInts($category, $value, $input, $hexWidth) $partNumber $part $hexWidth");
			$this->core->setNested($category, $value, array($partNumber, hexdec($part)));
		}
	}
	
	function intsToHex($hexWidth, $values)
	{
		if (!$hexWidth) $hexWidth=defaultHexWidth;
		$output='';
		
		foreach ($values as $value) $output.=$this->intToHex($value, $hexWidth);
		return $output;
	}
}

$core=core::assert();
$core->registerModule(new Base());



?>