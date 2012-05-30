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
		
		$this->core->set('Codes', 'testcolor', "This shows that the color codes have been loaded.");
	}
}

$core=core::assert();
$core->registerModule(new Codes());
 
?>