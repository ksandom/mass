<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Provides terminal codes

class Codes extends Module
{
	function __construct()
	{
		parent::__construct('Codes');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->loadCodes();
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
	
	function loadCodes()
	{
		$this->loadControlCodes();
		$this->loadColorCodes();
	}
	
	function loadControlCodes()
	{
		$this->core->set('Codes', 'esc', "\e");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		
		$this->core->set('Codes', 'testControl', "This shows that the control codes have been loaded.");
	}
	
	function loadColorCodes()
	{
		$shortNamesBelongTo='dark';
		
		$deck=array(
			0=>'dark', 
			1=>'bright');
			
		$color=array(
			30=>'Black',
			31=>'Red',
			32=>'Green',
			33=>'Yellow',
			34=>'Blue',
			35=>'Purple',
			36=>'Cyan',
			37=>'White');
		
		foreach ($deck as $deckKey=>$deckName)
		{
			foreach ($color as $colorKey=>$colorName)
			{
				$colorCode="\033[$deckKey;{$colorKey}m";
				$this->core->set('Codes', "$deckName$colorName", $colorCode);
				
				if ($deckName==$shortNamesBelongTo)
				{ // give short names to the lover deck
					$shortname=strtolower($colorName);
					$this->core->set('Codes', $shortname, $colorCode);
				}
			}
		}
		
		$this->core->set('Codes', 'default', "\033[0;37m");
		
		$this->core->set('Codes', 'testColor', "This shows that the color codes have been loaded.");
	}
}

$core=core::assert();
$core->registerModule(new Codes());
 
?>