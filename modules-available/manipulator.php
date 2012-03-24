<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manipulate output

class Manipulator extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Manipulator');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('toString'), 'toString', 'Convert array or arrays into an array of strings.');
				$this->core->registerFeature($this, array('f', 'flatten'), 'flatten', 'Flatten an array or arrays into a keyed array of values. --flatten[=limit]. Note that "limit" specifies how far to go into the nesting before simply returning what ever is below.');
				break;
			case 'followup':
				break;
			case 'toString':
				return $this->toString($this->core->getSharedMemory());
				break;
			case 'flatten':
				return $this->flatten($this->core->getSharedMemory(), $this->core->get('Global', 'flatten'));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function toString($input)
	{
		return $input;
	}
	
	function flatten($input, $limit, $nesting=0)
	{
		$output=array();
		$clashes=array();
		$this->getArrayNodes($output, $input, $clashes, $limit, $nesting);
		return $output;
	}
	
	private function getArrayNodes(&$output, $input, &$clashes, $limit, $nesting)
	{
		echo "$limit, $nesting\n";
		foreach ($input as $key=>$value)
		{
			if (is_array($value) and !(is_numeric($limit) and ($nesting>=$limit)))
			{
				$this->getArrayNodes($output, $value, $clashes, $limit, $nesting+1);
			}
			else
			{
				if (is_numeric($key)) $output[]=$value;
				else
				{
					if (!isset($output[$key])) $output[$key]=$value;
					else
					{
						# work out new key based on clashes
						$clashes[$key]=(isset($clashes[$key]))?$clashes[$key]+1:1;
						$newKey="$key{$clashes[$key]}";
						echo "Chose key $newKey\n";
						$output[$newKey]=$value;
					}
					
				}
			}
		}
	}
}

$core=core::assert();
$core->registerModule(new Manipulator());
 
?>