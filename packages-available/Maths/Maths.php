<?php
# Copyright (c) 2013 Kevin Sandom under the BSD License. See LICENSE for full details.

class Maths extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Maths');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('basicMaths'), 'basicMaths', 'Apply a mathematical operator on two numbers and put the results into a store variable. --basicMaths=Category,variableName,value1,operator,value2', array('array', 'string'));
				break;
			case 'followup':
				break;
			case 'last':
				break;

			case 'basicMaths':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 5, $event, $originalParms, $parms);
				$this->core->set($parms[0], $parms[1], $this->basicMaths($parms[2], $parms[3], $parms[4]));
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function basicMaths($value1, $operator, $value2)
	{
		switch ($operator)
		{
			case '*': # Multiply
				return $value1*$value2;
				break;
			case '/': # Divide - Complain loudly on divide by 0.
				if ($value2!=0) return $value1/$value2;
				else
				{
					$this->core->debug(1, "Divide by zero in $value1,$operator,$value2 . Returning false.");
					return false;
				}
				break;
			case '/!': # Divide - Assume false on divide by 0.
				if ($value2!=0) return $value1/$value2;
				else
				{
					$this->core->debug(3, "Divide by zero in $value1,$operator,$value2 . Returning false since /! was specified.");
					return false;
				}
				break;
			case '/>': # Divide - Assume value2 (0) on divide by 0.
				if ($value2!=0) return $value1/$value2;
				else
				{
					$this->core->debug(3, "Divide by zero in $value1,$operator,$value2 . Assuming value2($value2) since the operator was />.");
					return $value2;
				}
				break;
			case '/<': # Divide - Assume value1 on divide by 0.
				if ($value2!=0) return $value1/$value2;
				else
				{
					$this->core->debug(3, "Divide by zero in $value1,$operator,$value2 . Assuming value1($value1) since the operator was /<.");
					return $value1;
				}
				break;
			case '+': # Add
				return $value1+$value2;
				break;
			case '-': # Subtract
				return $value1-$value2;
				break;
			case '%': # Modulus
				return $value1%$value2;
				break;
			case '^': # Exponent
				return $value1^$value2;
				break;
		}
	}
}

$core=core::assert();
$core->registerModule(new Maths());
 
?>