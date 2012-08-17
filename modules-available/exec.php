<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Run stuff!

class Exec extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Exec');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('e', 'exec'), 'exec', 'Blindly execute what ever we recieve. USE WITH CARE!');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'exec':
				$parm=$this->core->get('Global', 'exec');
				if ($parm) $this->run($parm);
				else $this->exec($this->core->getResultSet());
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function exec($input)
	{
		foreach ($input as $line)
		{
			if (is_string($line)) $this->run($line);
		}
	}
	
	function run($line)
	{ // Run stuff via this function so that it can easily be abstracted out later on.
		$this->core->debug(4, "EXEC  $line");
		return `$line`;
	}
	
}

$core=core::assert();
$core->registerModule(new Exec());
 
?>