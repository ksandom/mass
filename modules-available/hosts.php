<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage hosts

class Hosts extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Hosts');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->dataDir=$this->core->get('General', 'configDir').'/data';
				$hostFiles=$this->core->getFileList($this->dataDir.'/hosts');
				$allHostDefinitions=array();
				foreach ($hostFiles as $filename=>$hostFile)
				{
					$allHostDefinitions[$filename]=json_decode(file_get_contents($hostFile));
				}
				
				$this->core->set('Hosts', 'hostDefinitions', $allHostDefinitions);
				
				$this->core->registerFeature($this, array('l', 'list'), 'list', 'List/Search host entries.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'list':
				return $this->listHosts();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function hostMatches($host, $search)
	{
		if (!$search) return true; # If no search, return all results.
		
		foreach ($host as $key=>$detail)
		{
			#echo "detail $key\n";
			if (strpos($detail, $search)!==false)
			{
				return true;
			}
		}
	}
	
	function listHosts()
	{
		$output=array();
		
		$search=$this->core->get('Global', 'list');
		$allHostDefinitions=$this->core->get('Hosts', 'hostDefinitions');
		foreach ($allHostDefinitions as $filename=>$fileDetails)
		{
			foreach ($fileDetails as $categoryName=>$categoryDetails)
			{
				foreach ($categoryDetails as $hostName=>$hostDetails)
				{
					if ($this->hostMatches($hostDetails, $search))
					{
						$iip=(isset($hostDetails->internalIP))?$hostDetails->internalIP:false;
						$eip=(isset($hostDetails->externalIP))?$hostDetails->externalIP:false;
						
						$output[]=array('filename'=>$filename, 'categoryName'=>$categoryName, 'hostName'=>$hostName, 'internalIP'=>$iip, 'externalIP'=>$eip);
						#echo "$filename: $categoryName, $hostName i=$iip e=$eip\n";
						//echo "$filename: $categoryName, $hostName: i={$hostDetails['internalIP']} e={$hostDetails['externalIP']}\n";
					}
				}
			}
		}
		
		return $output;
	}

}

$core=core::assert();
$core->registerModule(new Hosts());
 
?>