<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage confiuration

class Data extends Module
{
	private $configDir=null;
	
	function __construct()
	{
		parent::__construct('Data');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('saveStoreToConfig'), 'saveStoreToConfig', 'Save all store values for a particular module name. Will be auto-loaded. --saveStoreToConfig=storeName');
				$this->core->registerFeature($this, array('loadStoreFromConfig'), 'loadStoreFromConfig', 'Load all store values for a particular module name. --loadStoreFromConfig=storeName');
				$this->core->registerFeature($this, array('saveStoreToData'), 'saveStoreToData', 'Save all store values for a particular module name. --saveStoreToData=storeName');
				$this->core->registerFeature($this, array('loadStoreFromData'), 'loadStoreFromData', 'Load all store values for a particular module name. --loadStoreFromData=storeName');
				$this->core->registerFeature($this, array('loadStoreFromFile'), 'loadStoreFromFile', 'Load all store values for a particular name from a file. Note that the file name MUST be in the form storeName.config.json where storeName is the destination name of the store that you want to save. This can be useful for importing config. --loadStoreFromFile=filename');
				$this->core->registerFeature($this, array('saveStoreToFile'), 'saveStoreToFile', 'Save all store values for a particular module name to a file. This can be useful for exporting data to other applications. --saveStoreToFile=Category,fullPathToFilename');

				$this->configDir=$this->core->get('General', 'configDir');
				$this->loadConfig();
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'saveStoreToConfig':
				$this->saveStoreEntry($this->core->get('Global', 'saveStoreToConfig'), 'config');
				break;
			case 'loadStoreFromConfig':
				$this->loadStoreEntryFromName($this->core->get('Global', 'loadStoreFromConfig'), 'config');
				break;
			case 'saveStoreToData':
				$this->saveStoreEntry($this->core->get('Global', 'saveStoreToData'), 'data');
				break;
			case 'loadStoreFromData':
				$this->loadStoreEntryFromName($this->core->get('Global', 'loadStoreFromData'), 'data');
				break;
			case 'loadStoreFromFile':
				$this->loadStoreEntryFromFilename($this->core->get('Global', 'loadStoreFromFile'));
				break;
			case 'saveStoreToFile':
				# TODO finish this. See help for details of how it will fit together.
				$this->saveStoreEntryToFilename($this->core->get('Global', 'saveStoreToFile'));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadStoreEntry($storeName, $filename, $source='config')
	{
		$filenameTouse=false;
		if (file_exists($filename)) $filenameTouse=$filename;
		elseif (file_exists($this->configDir."/$source/$filename")) $filenameTouse=$this->configDir."/$filename";
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
	
	function loadStoreEntryFromName($storeName, $source='config')
	{
		$filename=$this->configDir."/$source/$storeName.$source.json";
		return $this->loadStoreEntry($storeName, $filename);
	}

	function loadConfig()
	{
		$configFiles=$this->core->getFileList($this->configDir.'/config');
		foreach ($configFiles as $filename=>$fullPath)
		{
			$this->loadStoreEntryFromFilename($fullPath);
		}
	}
	
	function saveStoreEntry($storeName, $source='config')
	{
		if ($config=$this->core->getCategoryModule($storeName))
		{
			$fullPath="{$this->configDir}/$source/$storeName.$source.json";
			file_put_contents($fullPath, json_encode($config));
		}
	}
}

$core=core::assert();
$core->registerModule(new Data());
 
?>