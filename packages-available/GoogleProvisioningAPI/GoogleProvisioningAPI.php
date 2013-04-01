<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# GoogleProvisioningAPI integration

/*
Required
*/

@include_once 'Zend/Loader.php';

class GoogleProvisioningAPI extends Module
{
	private $ec2Connection=null;
	private $elbConnection=null;
	private $route53Connection=null;
	private $awsKey=null;
	private $awsSecret=null;
	private $foundLibrary;
	
	function __construct()
	{
		parent::__construct('OAuth');
		
		$this->foundLibrary=(class_exists('Zend_Gdata_ClientLogin') and class_exists('Zend_Gdata_Gapps'));
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				break;
			case 'last':
				# TODO update this to reflect the new ability to use multiple libraries.
				if (!$this->foundLibrary) $this->core->debug(1, "The OAuth library was not found.");
				break;
			case 'followup':
				$this->displayLibraryStatus();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function displayLibraryStatus()
	{
		if ($this->foundLibrary)
		{
			$this->core->debug(0, "Loaded GAPI");
		}
		else
		{
			$this->core->debug(0, "Not Loaded GAPI");
		}
	}
	
	function hasLibrary()
	{
		return $this->foundLibrary;
	}
}

$core=core::assert();

if (class_exists('Zend_Gdata_ClientLogin')) Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
if (class_exists('Zend_Gdata_Gapps')) Zend_Loader::loadClass('Zend_Gdata_Gapps');

$core->registerModule(new GoogleProvisioningAPI());
?>