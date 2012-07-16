<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage mass packages


class Packages extends Module
{
	private $loadedPackages=array();
	
	function __construct()
	{
		parent::__construct('Packages');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->loadEnabledPackages();
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
	
	function loadEnabledPackages()
	{
		$packageEnabledDir=$this->core->get('General', 'configDir').'/packages-enabled';
		$list=$this->core->getFileList($packageEnabledDir);
		foreach ($list as $filename)
		{
			$this->core->debug(0, "loadEnabledPackages: Loaded $filename");
		}
	}
	
	function loadPackages()
	{
	}
	
	function loadPackage($packageName)
	{
		if (!isset($this->loadedPackages[$packageName]))
		{
			
		}
		else
		{
			$this->core->debug(1, "loadPackage: $packageName is already loaded.");
		}
	}
}

$core=core::assert();
$core->registerModule(new Packages());
 
?>