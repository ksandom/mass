<?php
# Copyright (c) 2013, Kevin Sandom under the BSD License. See LICENSE for full details.

# GoogleProvisioningAPI integration

/*
Some parts of this code are heavily derived from documentation in https://developers.google.com/google-apps/provisioning/

Required
*/

@include_once 'Zend/Loader.php';

class GoogleProvisioningAPI extends Module
{
	private $client=null;
	private $email=false;
	private $password=false;
	private $domain=false;
	private $foundLibrary;
	
	function __construct()
	{
		parent::__construct('OAuth');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('GPAPISetCred'), 'GPAPISetCred', "Set the credentials for use with GoogleProvisioningAPI. --GPAPISetCred=domain,email,password eg --GPAPISetCred=example.com,dude@example.com,totallyRadDuuuuude", array('credentials', 'gapi'));
				$this->core->registerFeature($this, array('GPAPIGetUsers'), 'GPAPIGetUsers', "Get all users using the GoogleProvisioningAPI. --GPAPIGetUsers", array('users', 'gapi'));
				break;
			case 'last':
				break;
			case 'followup':
				break;
			case 'GPAPISetCred':
				if ($parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 3, false))
				{
					$this->email=$parms[0];
					$this->password=$parms[1];
				}
				break;
			case 'GPAPIGetUsers':
				$this->getAllUsers();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function getAllUsers()
	{
		if (!$this->assertLogin()) return false;
		
		$this->gdata->retrieveAllUsers();
	}
	
	function assertCredentials()
	{
		if ($this->email and $this->password and $this->domain) return true;
		else
		{
			$this->core->debug(1, "GoogleProvisioningAPI->assertCredentials: We don't have any credentials.");
		}
	}
	
	function assertLogin()
	{
		# NOTE The order of these two checks could be reversed to make failure more graceful in many situations. However it feels wrong. If a user adds their credentials and then doesn't bother to install the library, their credentials would be left around unnecessarily. This can be fixed by design, so is not necessarily wrong...
		if (!$this->assertLibrary()) return false;
		if (!$this->assertCredentials()) return false;
		
		if ($this->client=Zend_Gdata_ClientLogin::getHttpClient($this->email, $this->password, Zend_Gdata_Gapps::AUTH_SERVICE_NAME))
		{
			if ($this->gdata = new Zend_Gdata_Gapps($this->client, $this->domain))
			{
				return true;
			}
			else return false;
		}
		else
		{
			$this->core->debug(1, "GoogleProvisioningAPI->assertLogin: Was unable to log in.");
			return false;
		}
	}
	
	function assertLibrary()
	{
		$result=true;
		
		foreach (array('Zend_Gdata_ClientLogin', 'Zend_Gdata_Gapps') as $className)
		{
			if (!class_exists($className)) Zend_Loader::loadClass($className);
			if (!class_exists($className))
			{
				$this->core->debug(1, "GoogleProvisioningAPI->assertLibrary: Failed to load $className.");
				$result=false;
			}
		}
		
		$this->foundLibrary=$result;
		return $this->foundLibrary;
	}
	
	function hasLibrary()
	{
		return $this->foundLibrary;
	}
}

$core=core::assert();

$core->registerModule(new GoogleProvisioningAPI());
?>