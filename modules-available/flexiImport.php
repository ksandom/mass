<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Is fairly the inverse of a template

/*
	intended use:
		fiCreate sshConfig
		fiNewRecordOn sshConfig,keep,^Host .*
		
		fiRuleDefine sshConfig,host,^Host (.*)$
		fiRuleMap sshConfig,host,1,hostname
		
		fiRuleDefine sshConfig,hostname,^Hostname (.*)$
		fiRuleMap sshConfig,hostname,1,externalFQDN
		
		fiRuleDefine sshConfig,key,^IdentityFile (.*)$
		fiRuleMap sshConfig,key,1,key
		
		fiGo sshConfig
*/

class FlexiImport extends Module
{
	function __construct()
	{
		parent::__construct('FlexiImport');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('fiCreate'), 'fiCreate', "Create a named flexiImport set. See docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiDelete'), 'fiDelete', "Delete a named flexiImport set. See docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiRuleDefine'), 'fiRuleDefine', "Use a regular expression to pull out relevant parts of a matching line. See docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiRuleMap'), 'fiRuleMap', "Map the output of --fiRuleDefine. See docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiGo'), 'fiGo', "Run a named FlexiImport set on the current resultSet. See docs/importUsingFlexiImport.md", array('import'));

				break;
			case 'fiCreate':
				return $this->fiCreate($this->core->get('Global', 'fiCreate'));
				break;
			case 'fiDelete':
				return $this->fiDelete();
				break;
			case 'fiRuleDefine':
				return $this->fiRuleDefine();
				break;
			case 'fiRuleMap':
				return $this->fiRuleMap();
				break;
			case 'fiGo':
				return $this->fiGo();
				break;
			case 'last':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function fiCreate()
	{
	}
	
	function fiDelete()
	{
	}
	
	function fiRuleDefine()
	{
	}
	
	function fiRuleMap()
	{
	}
	
	function fiGo()
	{
	}
}

$core=core::assert();
$core->registerModule(new FlexiImport());
 
?>