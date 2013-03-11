<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Adds the ability to put conditions into macros

/*
	There are essentially two variants with one alias and each having their not equivilent
		* ifResultExists
		* ifNotEmptyResult ifResult <-- Most of the time you'll want this one

*/

class Condition extends Module
{
	function __construct()
	{
		parent::__construct('Condition');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('notIfResultExists'), 'notIfResultExists', "Will run the specified command if we don't have a result from something that had previously run. Note that is different from and empty result. --notIfResultExists=\"command[ arguments]\" .", array('language'));
				$this->core->registerFeature($this, array('ifResultExists'), 'ifResultExists', '--ifResultExists="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('ifResult', 'notIfEmptyResult'), 'notIfEmptyResult', '--notIfEmptyResult="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('notIfResult', 'ifEmptyResult'), 'ifEmptyResult', '--ifEmptyResult="command[ arguments]" .', array('language'));
				$this->core->registerFeature($this, array('if'), 'if', '--if=value1,comparison,value2,command[,arguments] . Comparison could be ==, !=, >, >=, <, <= .', array('language'));
				// $this->core->setFeatureAttribute('if', 'indentFeature', 'lastIf');
				$this->core->registerFeature($this, array('lastIf'), 'lastIf', 'Do the last condition. Currently only supported by --if --lastIf=command[, arguments] .', array('language'));
				$this->core->registerFeature($this, array('elseIf'), 'elseIf', '--elseIf=value1,comparison,value2,command[,arguments] . Comparison could be ==, !=, >, >=, <, <= .', array('language'));
				$this->core->setFeatureAttribute('elseIf', 'indentFeature', 'lastIf');
				$this->core->registerFeature($this, array('else'), 'else', 'Do the inverse of the last condition. Currently only supported by --if --else=command[, arguments] .', array('language'));
				$this->core->setFeatureAttribute('else', 'indentFeature', 'else');
				$this->core->registerFeature($this, array('resetIf'), 'resetIf', 'Reset the status of the last --if. This means that both --lastIf and --else will not evaluate to true.', array('language'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'notIfResultExists':
				return $this->ifResultExists($this->core->getResultSet(), $this->core->get('Global', 'notIfResultExists'), false);
				break;
			case 'ifResultExists':
				return $this->ifResultExists($this->core->getResultSet(), $this->core->get('Global', 'ifResultExists'), true);
				break;
			case 'notIfEmptyResult':
				return $this->ifNotEmptyResult($this->core->getResultSet(), $this->core->get('Global', 'notIfEmptyResult'), true);
				break;
			case 'ifEmptyResult':
				return $this->ifNotEmptyResult($this->core->getResultSet(), $this->core->get('Global', 'ifEmptyResult'), false);
				break;
			case 'if':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4, true);
				if ($this->doIf($parms[0], $parms[1], $parms[2]))
				{
					return $this->core->callFeature($parms[3], $parms[4]);
				}
				break;
			case 'elseIf':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4, true);
				$conditionMatched=$this->core->get('Me', 'conditionMatched');
				if ($conditionMatched===false)
				{
					if ($this->doIf($parms[0], $parms[1], $parms[2]))
					{
						return $this->core->callFeature($parms[3], $parms[4]);
					}
				}
				break;
			case 'else':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1, true);
				if ($this->core->get('Me', 'conditionMatched')===false)
				{
					$this->core->set('Me', 'conditionMatched', true);
					return $this->core->callFeature($parms[0], $parms[1]);
				}
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function ifResultExists(&$input, $parms, $match=true)
	{
		if ((is_array($input)) == $match)
		{
			$this->takeAction($input, $parms);
		}
		else return false;
	}

	function ifNotEmptyResult($input, $parms, $match=true)
	{
		$matchValue=($match)?'true':'false';
		
		$matched=false;
		$result=(workAroundIfBug)?$input:false; // See doc/bugs/ifBug.md
		
		if (is_array($input) and count($input))
		{
			// This is to make sure that the first value is of significance. ie not empty.
			$keys=array_keys($input);
			if (($input[$keys[0]])) $matched=true;
		}
		
		/*
			The problem:
				The non-return of a result is replacing a legitimate result. ie when one if condition runs, the dataset isn't available to the second condition.
				
				I can hack around the problem by returning the input with the appropriate failure here, but the problem is not here and should not be solved here. It's somewhere around something calling this.
		*/
		if ($matched == $match)
		{
			$this->core->incrementNesting();
			$result=$this->takeAction($parms);
			$this->core->decrementNesting();
		}
		# TODO device if this should stay or not once the problem is solved.
		//else $result=$input;
		#else $result=false;
		
		# TODO The problem is actually with the results getting lost between calls.
		
		if ($this->core->isVerboseEnough(5))
		{
			if (is_bool($result)) $inputType="isbool";
			if (is_null($result)) $inputType="isnull";
			if (is_array($result)) $inputType="isarray";
			if (is_array($result)) $resultType="isarray";
			else $resultType="isnotarray";
			
			print_r(array('inputType'=>$inputType, 'input'=>$input, 'parms'=>$parms, 'match'=>$match, 'matched'=>$matched, 'resultType'=>$resultType, 'result'=>$result));
			
		}
		
		
		$this->core->debug(5, "ifNotEmptyResult: Just about to return. Count=".$this->core->getResultSetCount());
		return $result;
	}
	
	function doIf($value1, $comparison, $value2)
	{
		switch ($comparison)
		{
			case '==':
				$result=($value1==$value2);
				break;
			case '!=':
				$result=($value1!=$value2);
				break;
			case '>':
				$result=($value1>$value2);
				break;
			case '<':
				$result=($value1<$value2);
				break;
			case '>=':
				$result=($value1>=$value2);
				break;
			case '<=':
				$result=($value1<=$value2);
				break;
			default:
				$this->core->debug(0, "Condition: Unknown comparison \"$comparison\" in \"$value1,$comparison,$value2\"");
				$result=false;
				break;
		}
		
		$this->core->set('Me', 'conditionMatched', $result);
		
		return $result;
	}
	
	function takeAction($parms)
	{
		$parmParts=$this->core->splitOnceOn(' ', $parms);
		return $this->core->callFeature($parmParts[0], $parmParts[1]);
	}
}

$core=core::assert();
$core->registerModule(new Condition());
 
?>