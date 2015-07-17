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


class AWS extends Module
{
	private $ec2Connection=null;
	private $elbConnection=null;
	private $route53Connection=null;
	private $awsKey=null;
	private $awsSecret=null;
	private $foundLibrary;
	
	function __construct()
	{
		parent::__construct('AWS');
		
		$this->foundLibrary=class_exists('AmazonEC2');;
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('AWSSetCred'), 'AWSSetCred', "Set the AWS credentials. --AWSSetCred=awsKey,awsSecret", array('credentials'));
				$this->core->registerFeature($this, array('AWSGetRegions'), 'AWSGetRegions', "Get the AWS regions", array('import'));
				$this->core->registerFeature($this, array('AWSGetHosts'), 'AWSGetHosts', "Get all running AWS instances", array('import'));
				$this->core->registerFeature($this, array('AWSGetAllHosts'), 'AWSGetAllHosts', "Get all AWS instances (even powered off ones).", array('import'));
				$this->core->registerFeature($this, array('AWSGetELBs'), 'AWSGetELBs', "Get all Elastic Load Balancers", array('import'));
				$this->core->registerFeature($this, array('AWSGetRoute53'), 'AWSGetRoute53', "Get all Route53 entries.", array('import'));
				$this->core->registerFeature($this, array('AWSCloseConnection'), 'AWSCloseConnection', "Close any open connections to AWS so that a new one can be created.", array('import'));
				$this->core->registerFeature($this, array('AWSLibraryDetails'), 'AWSLibraryDetails', "Get information about the AWS library like where mass is expecting to find it.", array('import'));
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
			case 'AWSGetELBs':
				if ($this->hasLibrary()) return $this->AWSGetELBsForAllRegions();
				else $this->warn();
				break;
			case 'AWSGetRoute53':
				if ($this->hasLibrary()) return $this->AWSGetDNSEntriesForAllRegions();
				else $this->warn();
				break;
			case 'AWSCloseConnection':
				if ($this->hasLibrary()) $this->AWSCloseConnection();
				else $this->warn();
				break;
			case 'AWSLibraryDetails':
				return $this->AWSLibraryDetails();
				break;
			case 'last':
				# TODO update this to reflect the new ability to use multiple libraries.
				if (!$this->foundLibrary) $this->core->debug(1, "The AWS library was not found.");
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
		# TODO update this to reflect the new ability to use multiple libraries.
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
			$this->elbConnection = new AmazonELB(array('key'=>$key, 'secret'=>$secret));
			
			# TODO This will unboubtedly change before it makes it into the official AWS PHP SDK. Therefore the required code is commented out below.
			#if (class_exists('AmazonRoute53')) $this->route53Connection = new AmazonRoute53(array('key'=>$key, 'secret'=>$secret));
			if (class_exists('AmazonRoute53')) $this->route53Connection = new AmazonRoute53($key, $secret);
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
		$this->core->debug(3, "AWSInternalGetRegions: Getting ec2 regions");
		$this->AWSAssertConnection();
		$response=$this->ec2Connection->describe_regions();
		$responseArray=$response->body->regionInfo->to_array();
		$arrayCopy=$responseArray->getArrayCopy();
		$rawRegions=$arrayCopy['item'];
		$regions=array();
		
		foreach ($rawRegions as $region)
		{
			$regionName=$region['regionName'];
			if (!isset($regions[$regionName])) $regions[$regionName]=array();
			
			$regions[$regionName]['regionName']=$region['regionName'];
			$regions[$regionName]['ec2RegionEndpoint']=$region['regionEndpoint'];
			$regions[$regionName]['elbRegionEndpoint']=str_replace('ec2', 'elasticloadbalancing', $region['regionEndpoint']); // TODO This is a hack to fill in missing AWS functionality. It will break sooner or later. See if there is a better way of doing it.
		}
		
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
			$endPoint=$region['ec2RegionEndpoint'];
			$regionName=$region['regionName'];
			
			$this->core->debug(1, "AWSGetHostsForAllRegions: Getting hosts/instances for $regionName via $endPoint");
			
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
					if (is_array($item))
					{
						if (isset($item['instancesSet']['item']['tagSet']['item']))
						{ // Most common structure
							$this->AWSProcessInstance($item, $output);
						}
						else
						{
							if (isset($item['instancesSet']['item'][0]['tagSet']['item']))
							{ // Instance set (batch)
								foreach ($item['instancesSet']['item'] as $subItem)
								{
									# TODO This is a horrific hack. Re-write in mass.
									$fakeItem=array('instancesSet'=>array('item'=>$subItem));
									$this->AWSProcessInstance($fakeItem, $output);
								}
							}
							elseif (isset($item['item']['tagSet']['item']))
							{ // I don't know how we got this, but we have a host that returns like this.
								$fakeItem=array('instancesSet'=>$item);
								$this->AWSProcessInstance($fakeItem, $output);
							}
							elseif (isset($item['item'][0]['tagSet']['item']))
							{ // I've never struck this, but given the above combinations, it seems logical to plan for.
								foreach ($item['item'] as $subItem)
								{
									$fakeItem=array('instancesSet'=>array('item'=>$subItem));
									$this->AWSProcessInstance($fakeItem, $output);
								}
							}
							elseif (isset($item['item']['groupId']) && count($item['item'])==2)
							{
								if ($this->core->isVerboseEnough(4))
								{
									$this->core->debug(3, "AWSGetHostsForAllRegions: Got what looks like something we can ignore:");
									print_r($item);
								}
								else $this->core->debug(3, "AWSGetHostsForAllRegions: Got what looks like something we can ignore. Increment verbosity with -v one more time to see what it is.");
							}
							else
							{
								$this->core->debug(3, "AWSGetHostsForAllRegions: Got an array, but don't know what to do with it. Here is the content:");
								if ($this->core->isVerboseEnough(3)) print_r($item);
							}
						}
					}
					else $this->core->debug(3, "AWSGetHostsForAllRegions: Got a weird result in $regionName: $item");
				}
			}
		}
		
		return $output;
	}
	
	function AWSProcessInstance($item, &$output)
	{
		# TODO This structure is getting silly. It's time to move this into mass.
		
		
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
		$name='';
		
		$name=$this->AWSMagicDemangle($host['tagSet']);
		$instanceID=(isset($item['instancesSet']['item']['instanceId']))?$item['instancesSet']['item']['instanceId']:'unknown';
		
		if ($name)
		{
			$host['hostName']=$name; # TODO check this!
			$this->core->debug(3, "AWSGetHostsForAllRegions: Instance $instanceID is $name.");
			
			# Re-map a couple of keys, then remove them so people don't use them creating non-portable code.
			if (isset($item['instancesSet']['item']['privateDnsName']))
			{
				$host['internalFQDN']=$item['instancesSet']['item']['privateDnsName'];
			}
			else $host['internalFQDN']='';
			
			if (isset($item['instancesSet']['item']['dnsName']))
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
			}
			elseif ($includePoweredOffInstances) 
			{
				$host['internalIP']='';
			}
			
			if (isset($output[$name]))
			{
				$originalInstanceID=$output[$name]['instanceId'];
				$newInstanceID=$host['instanceId'];
				
				$this->core->debug(0, "AWS->AWSGetHostsForAllRegions: Found a duplicate instance ($originalInstanceID) with the name tag \"$name\" while trying to add $newInstanceID. This will cause you much pain. For now this host will be added anonymously, but you really should fix this then run the import again.");
				$output[]=$host;
			}
			else $output[$name]=$host;
			
			
			if (isset($item['placement']))
			{
				if (isset($item['placement']['availabilityZone']))
				{
					$host['fullAvailabilityZone']=$item['placement']['availabilityZone'];
					$host['region']=substr($item['placement']['availabilityZone'], 0, strlen($item['placement']['availabilityZone'])-1);
					$host['availabilityZone']=substr($item['placement']['availabilityZone'], -1);
				}
			}
		
		unset($host);
		}
		else
		{
			$instanceID=$host['instanceId'];
			$this->core->debug(2, "AWSGetHostsForAllRegions: Did not find a name tag for instance $instanceID");
			print_r($host['tagSet']);
		}
	}
	
	function AWSGetELBsForAllRegions()
	{
		$this->core->debug(4, "AWSGetELBsForAllRegions: ENTERED");
		$regions=$this->AWSGetRegions();
		
		$this->core->debug(3, "AWSGetELBsForAllRegions: Ready to find Elastic Load Balancers");
		
		$output=array();
		$this->AWSAssertConnection();
		
		foreach ($regions as $region)
		{
			$this->core->debug(1, "AWSGetELBsForAllRegions: Scanning region {$region['regionName']}");
			$this->elbConnection->set_region($region['elbRegionEndpoint']);
			
			$response=$this->elbConnection->describe_load_balancers();
			$responseArrayObject=$response->body->DescribeLoadBalancersResult->to_array();
			$usefulArray=$responseArrayObject->getArrayCopy();
		
			if (isset($usefulArray['LoadBalancerDescriptions']['member']))
			{
				foreach ($usefulArray['LoadBalancerDescriptions']['member'] as $loadBalancer)
				{
					foreach (array('DNSName', 'CanonicalHostedZoneName', 'LoadBalancerName') as $id)
					{
						if (isset($loadBalancer[$id]))
						{
							$output[$loadBalancer[$id].$region['regionName']]=$loadBalancer;
							$output[$loadBalancer[$id].$region['regionName']]['Region']=$region['regionName'];
						}
					}
				}
			}
		}
		
		return $output;
	}
	
	function AWSGetDNSEntriesForAllRegions()
	{
		$this->core->debug(4, "AWSGetDNSEntriesForAllRegions: ENTERED");
		#$regions=$this->AWSGetRegions();
		
		$this->core->debug(3, "AWSGetDNSEntriesForAllRegions: Ready to find DNS entries");
		
		$output=array();
		$this->AWSAssertConnection();
		
		if (!$this->route53Connection)
		{
			$this->core->debug(2, "The route53 library is not loaded, and is therefore probably not included with the version of the AWS PHP SDK you have. It certainly isn't in the official one at the time of this writing.");
			return false;
		}
		
		$response=$this->route53Connection->list_hosted_zone();
		$responseArrayObject=$response->body->HostedZones->to_array();
		$hostedZones=$responseArrayObject->getArrayCopy();
		
		$output=array();
		
		foreach ($hostedZones['HostedZone'] as $hostedZone)
		{
			$this->core->debug(1, "AWSGetDNSEntriesForAllRegions: Getting DNS entries for {$hostedZone['Name']}");
			
			$idParms=explode('/', $hostedZone['Id']);
			$rrset=$this->route53Connection->list_rrset($idParms[2]);
			$responseArrayObject=$rrset->body->ResourceRecordSets->to_array();
			$recordSets=$responseArrayObject->getArrayCopy();
			
			foreach ($recordSets['ResourceRecordSet'] as $recordSet)
			{
				$output[]=$recordSet;
			}
		}
		
		return $output;
	}
	
	function AWSLibraryDetails()
	{
		$this->core->callFeature('nested');
		return array(
			'seekLocation'=>AWSLibrary,
			'found'=>$this->foundLibrary
		);
	}
	
	function AWSMagicDemangle($input, $searchValue='Name', $searchKey='key', $fetchKey='value')
	{ /* This performs kungfoo magic to demangle the messaged up data that comes from AWS.*/
		
		if (is_array($input))
		{
			if (isset($input[$searchKey]))
			{
				if (isset($input[$fetchKey]))
				{
					if ($input[$searchKey]==$searchValue) return $input[$fetchKey];
				}
			}
			
			foreach ($input as $item)
			{
				$result=$this->AWSMagicDemangle($item, $searchValue, $searchKey, $fetchKey);
				if ($result) return $result;
			}
			
			return false;
		}
		else
		{
			return false;
		}
	}
}

$core=core::assert();

$configDir=$core->get('General', 'configDir');

$awsLibrary=array(
	'installedViaManageMass'=>"$configDir/repos/forkedForkedAws-sdk-for-php/sdk.class.php",
	'installedViaMass'=>"$configDir/repos/aws-sdk-for-php/sdk.class.php",
	'obsoleteInstalledViaMass'=>"$configDir/externalLibraries/aws-sdk-for-php/sdk.class.php",
	'installedViaApt'=>'/usr/share/php/AWSSDKforPHP/sdk.class.php'
);


foreach ($awsLibrary as $title=>$file)
{
	if (file_exists($file))
	{
		@include_once($file);
		$core->set('AWS', 'libraryTitle', $title);
		$core->set('AWS', 'libraryFile', $file);
		break;
	}
}


$core->registerModule(new AWS());
 
?>