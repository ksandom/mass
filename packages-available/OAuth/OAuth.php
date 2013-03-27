<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# OAuth integration

/*
Required
*/


class OAuth extends Module
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
		
		$this->foundLibrary=class_exists('OAuth2\Client');;
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
}

$core=core::assert();

$configDir=$core->get('General', 'configDir');

$oAuthLibrary=array(
	'installedViaManageMass'=>"$configDir/repos/oauth_2.0_client_php"
);

foreach ($oAuthLibrary as $title=>$directory)
{
	if (file_exists($directory))
	{
		$filesToLoad=array('Client.php',
			'DataStore.php',
			'Exception.php',
			'HttpClient.php',
			'Service.php',
			'Token.php');
		
		foreach ($filesToLoad as $file)
		{
			include "$directory/OAuth2/$file";
		}
		
		include 'extra/OAuthImplementationStuff.php';
		
		$core->set('OAuth', 'libraryTitle', $title);
		$core->set('OAuth', 'libraryFile', $directory);
		break;
	}
}


$core->registerModule(new OAuth());
 
?>