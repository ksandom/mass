<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Amazon Web Services integration

define('AWSLibrary', '/usr/share/php/AWSSDKforPHP/sdk.class.php');

class AWS extends Module
{
	private $ec2Connection=null;
	
	function __construct()
	{
		parent::__construct('AWS');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('AWSGetRegions'), 'AWSGetRegions', "Get the AWS regions", array('import'));
				break;
			case 'getRegions':
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
	
	function AWSConnect()
	{
		$this->ec2Connection = new AmazonEC2();
	}
	
	function AWSGetRegions()
	{
		$regions=$this->ec2Connection->describe_regions();
		$items=$regions->body->regionInfo->item;
		
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