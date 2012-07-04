<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Amazon Web Services integration

define('AWSLibrary', '/usr/share/php/AWSSDKforPHP/sdk.class.php');

class AWS extends Module
{
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