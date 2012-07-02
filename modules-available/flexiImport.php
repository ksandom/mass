<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Is fairly the inverse of a template

/*
	intended use:
		fiCreate sshConfig
		fiNewRecordOn keep,^Host .*
		
		fiRuleDefine host,^Host (.*)$
		fiRuleMap host,1,hostname
		
		fiRuleDefine hostname,^Hostname (.*)$
		fiRuleMap hostname,1,externalFQDN
		
		fiRuleDefine key,^IdentityFile (.*)$
		fiRuleMap key,1,key
		
		fiGo
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
				$this->core->registerFeature($this, array('fiCreate'), 'fiCreate', "Create a named flexiImport set.", array('import'));
				$this->core->registerFeature($this, array('fiDelete'), 'fiDelete', "Delete a named flexiImport set.", array('import'));
				$this->core->registerFeature($this, array('fiRuleDefine'), 'fiRuleDefine', "", array('import'));
				$this->core->registerFeature($this, array('fiRuleMap'), 'fiRuleMap', "", array('import'));
				$this->core->registerFeature($this, array('fiGo'), 'fiGo', "", array('import'));

				break;
			case 'followup':
				break;
			case 'last':
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

}

$core=core::assert();
$core->registerModule(new FlexiImport());
 
?>