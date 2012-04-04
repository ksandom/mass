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
				$this->configDir=$this->core->get('General', 'configDir').'/config';
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
		$configFiles=$this->core->getFileList($this->configDir.'/config');
		foreach ($configFiles as $filename=>$fullPath)
		{
			$filenameParts=explode('.', $filename);
			$config=json_decode(file_get_contents($fullPath));
			$this->core->setStore($filenameParts[0], $config);
		}
	}
	
	function saveStoreEntry($storeName)
	{
		$config=$this->core->getStore($storeName);
		$fullPath="{$this->configDir}/$storeName.config.json"
		file_put_contents($fullPath, $config);
	}
}

$core=core::assert();
$core->registerModule(new Config());
 
?>