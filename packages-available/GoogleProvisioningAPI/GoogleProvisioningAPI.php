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
		parent::__construct('GoogleProvisioningAPI');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('GPAPISetCred'), 'GPAPISetCred', "Set the credentials for use with GoogleProvisioningAPI. --GPAPISetCred=domain,email,password eg --GPAPISetCred=example.com,dude@example.com,totallyRadDuuuuude", array('credentials', 'gapi'));
				$this->core->registerFeature($this, array('GPAPIGetUserToResultSet'), 'GPAPIGetUserToResultSet', "Get a specific user using the GoogleProvisioningAPI. --GPAPIGetUserToResultSet[=username] . If a username is not specified, all the users will be retrieved.", array('users', 'gapi'));
				$this->core->registerFeature($this, array('GPAPIGetGroupToResultSet'), 'GPAPIGetGroupToResultSet', "Get a specific group using the GoogleProvisioningAPI. --GPAPIGetUserToResultSet=user . If a user is not specified, all the groups will be retrieved.", array('users', 'gapi'));
				break;
			case 'last':
				break;
			case 'followup':
				break;
			case 'GPAPISetCred':
				if ($parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 3, true))
				{
					$this->domain=$parms[0];
					$this->email=$parms[1];
					$this->password=$parms[2];
				}
				else $this->core->debug(1, "GPAPISetCred: Insufficient parameters?");
				break;
			case 'GPAPIGetUserToResultSet':
				return $this->getAllUsers($this->core->get('Global', $event));
				break;
			case 'GPAPIGetGroupToResultSet':
				return $this->getAllGroups($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function getAllUsers($specificUser=false)
	{
		if (!$this->assertLogin()) return false;
		
		if ($specificUser) $result=$this->gdata->retrieveUser($specificUser);
		else $result=$this->gdata->retrieveAllUsers();
		return $this->core->objectToArray($result);
	}
	
	function getAllGroups($specificUser=false)
	{
		if (!$this->assertLogin()) return false;
		
		if ($specificUser) $result=$this->gdata->retrieveGroup($specificUser);
		else $result=$this->gdata->retrieveAllGroups();
		return $this->core->objectToArray($result);
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
		
		$reposDir=$this->core->get('General', 'configDir')."/repos";
		$prefix="libzend-framework-php";
		include_once "$prefix/Zend/Loader.php";
		
		foreach (array('Zend_Gdata_ClientLogin', 'Zend_Gdata_Gapps') as $className)
		{
			$this->core->debug(2, "GoogleProvisioningAPI->assertLibrary: Loading $className");
			//if (!class_exists($className)) Zend_Loader::loadClass($className);
			
			$parts=explode('_', $className);
			$fileName=implode('/', $parts).'.php';
			
			if (!class_exists($className)) include_once "$prefix/$fileName";
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