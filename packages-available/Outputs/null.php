<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Send data to the black hole of awesome.

class NullData extends Module
{
	function __construct()
	{
		parent::__construct('Null');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				// This isn't ready for usage yet.
				$this->core->registerFeature($this, array('q', 'quiet', 'null'), 'null', "Default verbosity to -1 so that debugging messages won't be shown unless the verbosity is changed. Typically you only use this in a script that takes mass output and does something with it. It shouldn't be used for normal operations. Note that for this to affect startup messages like \"Could not find a module to match\" it must be the first the very first parameter and not combined with any other parameters. eg -q will work, -qt will not. ~ verbosity,debug");
				$this->core->registerFeature($this, array('clearResults'), 'clearResults', 'Clear the result set.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'null':
				$this->core->setRef('General', 'outputObject', $this);
				break;
			case 'clearResults':
				return array();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function out($output)
	{
	}
}

$core=core::assert();
$core->registerModule(new NullData());
 
?>