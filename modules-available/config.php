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
				$this->core->registerFeature($this, array('saveStore'), 'saveStore', 'Save all store values for a particular module name. --saveStore=storeName');
				$this->core->registerFeature($this, array('loadStore'), 'loadStore', 'Load all store values for a particular module name. --loadStore=storeName');
				$this->core->registerFeature($this, array('loadStoreFromFile'), 'loadStoreFromFile', 'Load all store values for a particular name from a file. Note that the file name MUST be in the form storeName.config.json where storeName is the destination name of the store that you want to save. This can be useful for importing config. --loadStoreFromFile=filename');

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
			case 'loadStore':
				$this->loadStoreEntryFromName($this->core->get('Global', 'loadStore'));
				break;
			case 'loadStoreFromFile':
				$this->loadStoreEntryFromFilename($this->core->get('Global', 'loadStoreFromFile'));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadStoreEntry($storeName, $filename)
	{
		$filenameTouse=false;
		if (file_exists($filename)) $filenameTouse=$filename;
		elseif (file_exists($this->configDir."/$filename")) $filenameTouse=$this->configDir."/$filename";
		else
		{
			$this->core->complain($this, "Could not find $filename.");
			return false;
		}
		
		$config=json_decode(file_get_contents($filenameTouse), 1);
		$this->core->setStoreModule($storeName, $config);
	}
	
	function loadStoreEntryFromFilename($filename)
	{
		# expecting storeName.config.json or /path/to/massHome/config/storeName.config.json
		
		// Strip off any path that may be there
		$fullFilenameParts=explode('/', $filename);
		$noPath=$fullFilenameParts[count($fullFilenameParts)-1];
		
		// Strip off just the store name
		$filenameParts=explode('.', $noPath);
		$storeName=$filenameParts[0];
		
		return $this->loadStoreEntry($storeName, $filename);
	}
	
	function loadStoreEntryFromName($storeName)
	{
		$filename=$this->configDir."/$storeName.config.json";
		return $this->loadStoreEntry($storeName, $filename);
	}

	function loadConfig()
	{
		$configFiles=$this->core->getFileList($this->configDir);
		foreach ($configFiles as $filename=>$fullPath)
		{
			$this->loadStoreEntryFromFilename($fullPath);
		}
	}
	
	function saveStoreEntry($storeName)
	{
		if ($config=$this->core->getStoreModule($storeName))
		{
			$fullPath="{$this->configDir}/$storeName.config.json";
			file_put_contents($fullPath, json_encode($config));
		}
	}
}

$core=core::assert();
$core->registerModule(new Config());
 
?>