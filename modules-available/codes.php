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
				$this->core->registerFeature($this, array('color', 'C'), 'color', 'Turn on colored output.', array('userExtra'));
				$this->core->registerFeature($this, array('nocolor', 'b'), 'nocolor', 'Turn off colored output.', array('userExtra'));

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'color':
				$this->loadColorCodes(true);
				break;
			case 'nocolor':
				$this->loadColorCodes(false);
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
		$this->loadDefaultAliases();
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
	
	function loadColorCodes($useColor=true)
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
				$colorCode=($useColor)?"\033[$deckKey;{$colorKey}m":'';
				$this->core->set('Codes', "$deckName$colorName", $colorCode);
				
				if ($deckName==$shortNamesBelongTo)
				{ // give short names to the lover deck
					$shortname=strtolower($colorName);
					$this->core->set('Codes', $shortname, $colorCode);
				}
			}
		}
		
		$colorCode=($useColor)?"\033[0;37m":'';
		$this->core->set('Codes', 'default', $colorCode);
		
		$this->core->set('Codes', 'testColor', "This shows that the color codes have been loaded.");
	}
	
	function loadDefaultAliases()
	{
		$this->core->set('Codes', 'debug0', $this->core->get('Codes', 'brightBlack'));
		$this->core->set('Codes', 'debug1', $this->core->get('Codes', 'brightRed'));
		$this->core->set('Codes', 'debug2', $this->core->get('Codes', 'red'));
		$this->core->set('Codes', 'debug3', $this->core->get('Codes', 'yellow'));
		$this->core->set('Codes', 'debug4', $this->core->get('Codes', 'green'));
		$this->core->set('Codes', 'debug5', $this->core->get('Codes', 'cyan'));
		
		$this->core->set('Codes', 'debug6', $this->core->get('Codes', 'brightBlue'));
		$this->core->set('Codes', 'debug7', $this->core->get('Codes', 'brightBlue'));
		$this->core->set('Codes', 'debug8', $this->core->get('Codes', 'brightBlue'));
		$this->core->set('Codes', 'debug9', $this->core->get('Codes', 'brightBlue'));
	}
}

$core=core::assert();
$core->registerModule(new Codes());
 
?>