<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Amazon Web Services integration

/*
Required
sudo apt-get install php-pear
sudo pear channel-discover pear.amazonwebservices.com
sudo pear install aws/sdk
sudo apt-get install php5-curl
*/



define('AWSLibrary', '/usr/share/php/AWSSDKforPHP/sdk.class.php');

class AWS extends Module
{
	private $ec2Connection=null;
	private $awsKey=null;
	private $awsSecret=null;
	private $foundLibrary;
	
	function __construct()
	{
		parent::__construct('AWS');
		
		# TODO Improce this to detect the class
		$this->foundLibrary=file_exists(AWSLibrary);;
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('AWSSetCred'), 'AWSSetCred', "Set the AWS credentials. --AWSSetCred=awsKey,awsSecret", array('credentials'));
				$this->core->registerFeature($this, array('AWSGetRegions'), 'AWSGetRegions', "Get the AWS regions", array('import'));
				$this->core->registerFeature($this, array('AWSGetHosts'), 'AWSGetHosts', "Get all running AWS instances", array('import'));
				$this->core->registerFeature($this, array('AWSGetAllHosts'), 'AWSGetAllHosts', "Get all AWS instances (even powered off ones). More often than not, you probably want --AWSGetInstances.", array('import'));
				$this->core->registerFeature($this, array('AWSCloseConnection'), 'AWSCloseConnection', "Close any open connections to AWS so that a new one can be created.", array('import'));
				break;
				break;
			case 'AWSSetCred':
				if ($this->hasLibrary())
				{
					if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 2)) $this->AWSSetCred($parms[0], $parms[1]);
				}
				else $this->warn();
				break;
			case 'AWSGetRegions':
				if ($this->hasLibrary()) return $this->AWSGetRegions();
				else $this->warn();
				break;
			case 'AWSGetHosts':
				if ($this->hasLibrary()) return $this->AWSGetHostsForAllRegions();
				else $this->warn();
				break;
			case 'AWSGetAllHosts':
				if ($this->hasLibrary()) return $this->AWSGetHostsForAllRegions(true);
				else $this->warn();
				break;
			case 'AWSCloseConnection':
				if ($this->hasLibrary()) $this->AWSCloseConnection();
				else $this->warn();
			case 'last':
				break;
			case 'followup':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function hasLibrary()
	{
		return $this->foundLibrary;
	}
	
	function warn()
	{
		$this->core->debug(0, "The AWS PHP library doesn't appear to be installed. I tried to find it at ".AWSLibrary);
	}
	
	function AWSSetCred($key, $secret)
	{
		$this->core->debug(3, "Setting AWS credentials to key=$key secret=*hidden*");
		$this->awsKey=$key;
		$this->awsSecret=$secret;
	}
	
	function AWSConnect()
	{
		$key=$this->awsKey;
		$secret=$this->awsSecret;
		
		if ($key and $secret)
		{
			$this->core->debug(2, "Connecting to AWS with key=$key secret=*hidden*");
			$this->ec2Connection = new AmazonEC2(array('key'=>$key, 'secret'=>$secret));
		}
		else
		{
			$this->core->debug(3, "Connecting to AWS without credentials");
			$this->ec2Connection = new AmazonEC2();
		}
	}
	
	function AWSCloseConnection()
	{
		$this->ec2Connection=null;
	}
	
	function AWSAssertConnection()
	{
		if (!$this->ec2Connection) $this->AWSConnect();
	}
	
	function AWSInternalGetRegions()
	{
		$this->core->debug(3, "AWSInternalGetRegions: Getting regions");
		$this->AWSAssertConnection();
		$response=$this->ec2Connection->describe_regions();
		$responseArray=$response->body->regionInfo->to_array();
		$arrayCopy=$responseArray->getArrayCopy();
		$regions=$arrayCopy['item'];
		$regionsCount=count($regions);
		
		$this->core->debug(2, "AWSInternalGetRegions: Got $regionsCount regions");
		
		return $regions;
	}
	
	function AWSGetRegions()
	{
		if ($regions=$this->core->get('AWS', 'regions')) return $regions;
		else
		{
			$regions=$this->AWSInternalGetRegions();
			$this->core->set('AWS', 'regions', $regions);
			return $regions;
		}
	}
	
	function AWSGetHostsForAllRegions($includePoweredOffInstances=false)
	{
		$this->core->debug(4, "AWSGetHostsForAllRegions: ENTERED");
		$regions=$this->AWSGetRegions();
		
		$this->core->debug(3, "AWSGetHostsForAllRegions: Ready to find hosts");
		
		$output=array();
		$this->AWSAssertConnection();
		
		# Loop through each region
		foreach ($regions as $region)
		{
			# Set up
			$endPoint=$region['regionEndpoint'];
			$regionName=$region['regionName'];
			
			$this->core->debug(2, "AWSGetHostsForAllRegions: Getting hosts/instances for $regionName via $endPoint");
			
			$this->ec2Connection->set_region($endPoint);
			
			# Get all the instances
			$request=array(
				'Filter' => array(
					array('Name'=>'instance-state-name', 'Value'=>'running'),
				)
			);
			$response=$this->ec2Connection->describe_instances($request);
			$responseArrayObject=$response->body->reservationSet->to_array();
			$usefulArray=$responseArrayObject->getArrayCopy();
			
			# Loop through each instance
			if (isset($usefulArray['item']))
			{
				foreach ($usefulArray['item'] as $item) 
				{
					#print_r($item);
					#die();
					if (is_array($item) && isset($item['instancesSet']['item']['tagSet']['item']))
					{
						# Make the structure more useful.
						$host=$item;
						foreach ($item['instancesSet']['item'] as $key=>$arraySet) 
						{
							$host[$key]=$arraySet;
						}
						unset($host['instancesSet']);
						
						# Get the name tag
						# TODO It looks like there is a key problem, so this may break when there is more than one tag. Test this.
						$tagKeys=array_keys($host['tagSet']);
						if (is_numeric($tagKeys[0]))
						{
							foreach ($host['tagSet'] as $tagKey=>$tag) 
							{
								if (isset($tag['key']))
								{
									if ($tag['key']=='Name')
									{
										$name=$tag['value'];
										$this->core->debug(3, "AWSGetHostsForAllRegions: Found $name");
									}
									else $this->core->debug(3, "AWSGetHostsForAllRegions: not found");
								}
								else $this->core->debug(3, "AWSGetHostsForAllRegions: Got unexpected value. # TODO investigate this further.");
							}
						}
						else
						{
							if (isset($host['tagSet']['item']['key']))
							{
								if ($host['tagSet']['item']['key']=='Name')
								{
									$name=$host['tagSet']['item']['value'];
								}
							}
						}
						
						$host['hostName']=$name; # TODO check this!
						
						# Re-map a couple of keys, then remove them so people don't use them creating non-portable code.
						if (isset($item['instancesSet']['item']['privateDnsName']))
						{
							$host['internalFQDN']=$item['instancesSet']['item']['privateDnsName'];
						}
						else $host['internalFQDN']='';
						
						if (isset($item['instancesSet']['item']['privateDnsName']))
						{
							$host['externalFQDN']=$item['instancesSet']['item']['dnsName'];
						}
						else $host['externalFQDN']='';
						
						if (isset($item['instancesSet']['item']['ipAddress']))
						{
							$host['externalIP']=$item['instancesSet']['item']['ipAddress'];
							unset($item['instancesSet']['item']['ipAddress']);
						}
						else $host['externalIP']='';
						
						if (isset($item['instancesSet']['item']['privateIpAddress']))
						{
							$host['internalIP']=$item['instancesSet']['item']['privateIpAddress'];
							unset($item['instancesSet']['item']['privateIpAddress']);
							$output[]=$host;
						}
						elseif ($includePoweredOffInstances) 
						{
							$host['internalIP']='';
							$output[]=$host;
						}
					}
					else
					{
						if (is_array($item)) $this->core->debug(3, "AWSGetHostsForAllRegions: No name tag on result in $regionName.");
						else $this->core->debug(3, "AWSGetHostsForAllRegions: Got a weird result in $regionName: $item");
					}
				}
			}
			
			# TODO check what we want the regionKey and region for in this context
		}
		
		return $output;
	}
}

@include_once(AWSLibrary);

$core=core::assert();
$core->registerModule(new AWS());
 
?>