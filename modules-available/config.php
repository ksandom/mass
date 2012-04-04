<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage confiuration

class Config extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Config');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->loadConfig();
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
	
	function loadConfig()
	{
	}
	
	function saveStoreEntry()
	{
	}
}

$core=core::assert();
$core->registerModule(new Config());
 
?>