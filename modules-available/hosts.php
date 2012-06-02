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
				
				$this->core->registerFeature($this, array('searchOld'), 'searchOld', 'Deprecated. List/Search host entries. ', array('user', 'deprecated'));
				$this->core->registerFeature($this, array('importFromHostsFile'), 'importFromHostsFile', 'Import host entries from a hosts file.', array('import'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'searchOld':
				return $this->listHosts();
				break;
			case 'importFromHostsFile':
				return $this->importFromHostsFile();
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
			#if (strpos($detail, $search)!==false)
			if (preg_match('/'.$search.'/', $detail))
			{
				return true;
			}
		}
	}
	
	function listHosts()
	{
		$output=array();
		
		$search=$this->core->get('Global', 'searchOld');
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
						$ifqdn=(isset($hostDetails->internalFQDN))?$hostDetails->internalFQDN:false;
						$efqdn=(isset($hostDetails->externalFQDN))?$hostDetails->externalFQDN:false;
						
						$output[]=array('filename'=>$filename, 'categoryName'=>$categoryName, 'hostName'=>$hostName, 'internalIP'=>$iip, 'externalIP'=>$eip, 'internalFQDN'=>$ifqdn, 'externalFQDN'=>$efqdn);
						#echo "$filename: $categoryName, $hostName i=$iip e=$eip\n";
						//echo "$filename: $categoryName, $hostName: i={$hostDetails['internalIP']} e={$hostDetails['externalIP']}\n";
					}
				}
			}
		}
		
		return $output;
	}
	
	
	
	function importFromHostsFile()
	{
		if (file_exists('/etc/hosts'))
		{
			if ($contents=file_get_contents('/etc/hosts')) return $this->processHostsFile($contents);
			else $this->core->complain($this, "Didn't get any contents from /etc/hosts. Permissions?");
		}
		else $this->core->complain($this, "Could not find /etc/hosts. Are you on a real computer?");
	}
	
	function processHostsFile($fileContents)
	{
		# TODO make this work for more types of host file
		/*
			This is a first stab at reading the hosts file. Feel free to add your own situations, but please keep it generic enough that it doesn't break the common situations.
		*/
		
		$output=array();
		$lines=explode("\n", $fileContents);
		foreach ($lines as $line)
		{
			$trimmedLine=trim($line);
			if ($trimmedLine)
			{
				if (substr($trimmedLine,0, 1)!='#')
				{
					# TODO one of the ranges of regex functions is deprecated. Check this isn't one.
					$line=preg_replace('/\ +/', "\t", $line);
					$line=preg_replace('/\#.*$/', "\t", $line);
					
					$parts=explode("\t", $line);
					$numberOfParts=count($parts);
					if ($numberOfParts>1)
					{
						$lineOutput=(isset($output[$parts[1]]))?$output[$parts[1]]:array();
						if (!isset($lineOutput['hostNameMap']))
						{
							$lineOutput['hostNameMap']=array();
							$lineOutput['hostNameCount']=0;
						}
						
						$ipKey=(strpos($parts[0], '.'))?'internalIP':'internalIPv6';
						$lineOutput[$ipKey]=$parts[0];
						$lineOutput['hostName']=$parts[1];
						
						for ($i=0; $i<$numberOfParts; $i++)
						{
							if (!(isset($lineOutput['hostNameMap'][$parts[$i]])) and $parts[$i]!=$lineOutput['hostName'] and (trim($parts[$i])))
							{
								$lineOutput['hostNameMap'][$parts[$i]]=$parts[$i];
								$lineOutput['hostNameCount']++;
								$lineOutput['hostName'.$lineOutput['hostNameCount']]=$parts[$i];
							}
						}
						
						$output[$lineOutput['hostName']]=$lineOutput;
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