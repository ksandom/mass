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
				$this->core->registerFeature($this, array('fiNewRecordOn'), 'fiNewRecordOn', "Use a regular to define when a new logical record begins. You can either discard or keep the match to be matched on ifRuleDefines. Se docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiRuleDefine'), 'fiRuleDefine', "Use a regular expression to pull out relevant parts of a matching line. See docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiRuleMap'), 'fiRuleMap', "Map the output of --fiRuleDefine. See docs/importUsingFlexiImport.md", array('import'));
				$this->core->registerFeature($this, array('fiGo'), 'fiGo', "Run a named FlexiImport set on the current resultSet. See docs/importUsingFlexiImport.md", array('import'));

				break;
			case 'fiCreate':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 1)) return $this->fiCreate($parms[0]);
				break;
			case 'fiDelete':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 1)) return $this->fiDelete($parms[0]);
				break;
			case 'fiNewRecordOn':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 3)) return $this->fiNewRecordOn($parms[0], $parms[1], $parms[2]);
				break;
			case 'fiRuleDefine':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 3)) return $this->fiRuleDefine($parms[0], $parms[1], $parms[2]);
				break;
			case 'fiRuleMap':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 4)) return $this->fiRuleMap($parms[0], $parms[1], $parms[2], $parms[3]);
				break;
			case 'fiGo':
				if ($parms=$this->core->getRequireNumParmsOrComplain($this, $event, 1)) return $this->fiGo($parms[0]);
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
	
	function fiCreate()
	{
		# fiCreate sshConfig
	}
	
	function fiDelete()
	{ # TODO Low priority
		# fiDelete sshConfig
	}
	
	function fiNewRecordOn()
	{
		# fiNewRecordOn sshConfig,keep,^Host .*
	}
	
	function fiRuleDefine()
	{
		# fiRuleDefine sshConfig,host,^Host (.*)$
	}
	
	function fiRuleMap()
	{
		# fiRuleMap sshConfig,host,1,hostname
	}
	
	function fiGo()
	{
		# fiGo sshConfig
	}
}

$core=core::assert();
$core->registerModule(new FlexiImport());
 
?>