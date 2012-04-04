<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage confiuration

class Config extends Module
{
	private $configDir=null;
	
	function __construct()
	{
		parent::__construct('Config');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('saveStore'), 'saveStore', 'Save all store values for a particular name. --saveStore=storeName');

				$this->configDir=$this->core->get('General', 'configDir').'/config';
				$this->loadConfig();
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'saveStore':
				$this->saveStoreEntry($this->core->get('Global', 'saveStore'));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadStoreEntry($storeName)
	{
		$filename=$this->configDir."/$storeName.config.json";
		if (file_exists($filename))
		{
			$config=json_decode(file_get_contents($filename));
			$this->core->setStore($storeName, $config);
		}
	}
	
	function loadStoreEntryFromFilename()
	{
	}
	
	function loadStoreEntryFromName()
	{
	}

	function loadConfig()
	{
		$configFiles=$this->core->getFileList($this->configDir);
		foreach ($configFiles as $filename=>$fullPath)
		{
			$filenameParts=explode('.', $filename);
			$config=json_decode(file_get_contents($fullPath));
			$this->core->setStore($filenameParts[0], $config);
		}
	}
	
	function saveStoreEntry($storeName)
	{
		if ($config=$this->core->getStore($storeName))
		{
			$fullPath="{$this->configDir}/$storeName.config.json"
			file_put_contents($fullPath, $config);
		}
	}
}

$core=core::assert();
$core->registerModule(new Config());
 
?>