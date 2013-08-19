<?php
# Copyright (c) 2013, Kevin Sandom under the BSD License. See LICENSE for full details.

# Type management

class Types extends Module
{
	function __construct()
	{
		parent::__construct('Types');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('getType'), 'getType', "Get the logical data type of a provided string. --getType=Category,varName,valueToCheck .", array('language'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'getType':
				if ($parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 3))
				{
					$this->core->set($parms[0], $parms[1], $this->getType($parms[2]));
				}
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function getType($value)
	{
		# TODO detect mass specific types
		if (is_numeric($value)) return 'number';
		elseif (is_array($value)) return 'array';
		elseif (is_bool($value)) return 'bool';
		
		// Fall back to gettype. This will probably always return string due to the way interpretParms currently works.
		return gettype($value);
	}
}

$core=core::assert();
$core->registerModule(new Types());
 
?>