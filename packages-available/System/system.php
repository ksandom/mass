<?php
# Copyright (c) 2013, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage hosts

class System extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('System');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('internalExec'), 'internalExec', 'Call a feature. This is useful if you want to execute a variable. You should think very carefully before using this as poorly written code would allow an attacker to executre arbitrary code.', array('feature'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'internalExec':
				$parts=$this->core->splitOnceOn(' ', $this->core->get('Global', $event));
				return $this->core->callFeature($parts[0], $parts[1]);;
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
}

$core=core::assert();
$core->registerModule(new System());
 
?>