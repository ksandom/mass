<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Amazon Web Services integration

define('AWSLibrary', '/usr/share/php/AWSSDKforPHP/sdk.class.php');

class AWS extends Module
{
	private $ec2Connection=null;
	private $awsKey=null;
	private $awsSecret=null;
	
	function __construct()
	{
		parent::__construct('AWS');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('AWSSetCred'), 'AWSSetCred', "Set the AWS credentials. --AWSSetCred=awsKey,awsSecret", array('credentials'));
				$this->core->registerFeature($this, array('AWSGetRegions'), 'AWSGetRegions', "Get the AWS regions", array('import'));
				break;
			case 'AWSSetCred':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 1));$this->AWSSetCred($parms[0], $parms[1]);
				break;
			case 'AWSGetRegions':
				return $this->AWSGetRegions();
				break;
			case 'last':
				break;
			case 'followup':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function AWSSetCred($key, $secret)
	{
		$this->awsKey=$key;
		$this->awsSecret=$secret;
	}
	
	function AWSConnect()
	{
		$this->ec2Connection = new AmazonEC2();
	}
	
	function AWSGetRegions()
	{
		$regions=$this->ec2Connection->describe_regions();
		$items=$regions->body->regionInfo->item;
		
		return $regions;
	}
	
	function AWSInternalGetRegions()
	{
		if ($regions=$this->core->get('AWS', 'regions')) return $regions;
		else
		{
			$regions=$this->AWSGetRegions();
			$this->core->set('AWS', 'regions', $regions);
			return $regions;
		}
	}
	
	function AWSGetHostsForAllRegions()
	{
		$regions=$this->AWSInternalGetRegions();
		
		# Loop through each region
		foreach ($regions as $regionKey=>$region)
		{
			# Set up
			$endPoint=$region->regionEndpoint;
			$regionName=$region->regionName;
			$this->ec2Connection->set_region($endPoint);
			
			# Get all the instances
			$request=array(
				'Filter' => array(
					array('Name'=>'instance-state-name', 'Value'=>'running'),
				)
			);
			$response=$this->ec2Connection->describe_instances($request);
			
			# Loop through each instance
			foreach ($response->body->reservationSet->item as $item) 
			{
				if (isset($item->instancesSet->item->tagSet->item->value))
				{
					# Get the name tag
					foreach ($item->instancesSet->item->tagSet->item as $tag) 
					{
						if ($tag->key=='Name')
						{
							$name=(string)$tag->value;
						}
					}
					
					$internalIP=(string)$item->instancesSet->item->privateIpAddress;
					$externalIP=(string)$item->instancesSet->item->ipAddress;

				}
			}
			
			# TODO check what we want the regionKey and region for in this context
		}
	}
}

if (file_exists(AWSLibrary))
{
	if (@include_once(AWSLibrary))
	{
		$core=core::assert();
		$core->registerModule(new AWS());
	}
}
 
?>