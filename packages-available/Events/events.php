<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Events handeling

class Events extends Module
{
	private $loadedPackages=array();
	
	function __construct()
	{
		parent::__construct('Events');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('registerForEvent'), 'registerForEvent', "Register a feature to be executed when a particular event is triggered. --registerEvent=Category,eventName,featureName[,featureValue]", array());
				$this->core->registerFeature($this, array('triggerEvent'), 'triggerEvent', "Trigger an event. --triggerEvent=Category,eventName", array());
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'registerForEvent':
				$parms=$this->core->interpretParms($this->core->get('Global', 'registerForEvent'), 4, 3, true);
				$this->registerForEvent($parms[0], $parms[1], $parms[2], $parms[3]);
				break;
			case 'triggerEvent':
				$parms=$this->core->interpretParms($this->core->get('Global', 'triggerEvent'), 2, 2, true);
				return $this->triggerEvent($parms[0], $parms[1]);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function registerForEvent($category, $eventName, $featureName, $featureValue='', $priority=50)
	{
		$priorityGroups=$this->core->get($category, $eventName);
		if (!isset($priorityGroups[$priority])) $priorityGroups[$priority]=array();
		$priorityGroups[$priority][]=array('featureName'=>$featureName, 'featureValue'=>$featureValue);
		
		$this->core->debug(3, "Registered \"$featureName $featureValue\" to event \"$category, $eventName\" at priority $priority.");
		$this->core->set($category, $eventName, $priorityGroups);
	}
	
	
	function unRegisterEvent($category, $eventName, $featureName)
	{
		# TODO write this. It will be useful for unloading code.
	}
	
	function setPriority($category, $eventName, $featureName, $priority=50)
	{
		# TODO Write this. If this becomes relied on a lot, check to see if tasks should actually be part of macros. I envisage priorities being used when something HAS to be done first or last. Eg preparing folders for downloads, or cleaning up afterwards.
	}
	
	function triggerEvent($category, $eventName)
	{
		$this->core->debug(3, "triggerEvent: $category,$eventName");
		$priorityGroups=$this->core->get($category, $eventName);
		if (is_array($priorityGroups) && count($priorityGroups)>0)
		{
			foreach ($priorityGroups as $priority=>$priorityGroup)
			{
				if (count($priorityGroup))
				{
					$nesting=$this->core->incrementNesting();
					
					foreach ($priorityGroup as $eventee)
					{
						$this->core->debug(2, "triggerEvent: $category,$eventName: --{$eventee['featureName']}={$eventee['featureValue']}");
						$result=$this->core->callFeature($eventee['featureName'], $eventee['featureValue']);
						$this->core->setResultSet($result); // This is necessary because the feature being called may rely on it being there.
					}
					
					$resultSet=$this->core->getResultSet();
					$this->core->decrementNesting();
					return $resultSet;
				}
				else
				{
					$this->core->debug(4, "Removing priority group $priority from event \"$category, $eventName\" as it has no eventees.");
					unset($priorityGroups['priority']);
					
					# This is potentially inefficient. But there would have to be a LOT of priority groups for it to matter. If it becomes an issue, set a flag and do it at the end.
					$this->core->set($category, $eventName);
				}
			}
		}
		else
		{
			if (is_array($priorityGroups))
			{
				$this->core->debug(4, "Event \"$category, $eventName\" triggered, but there were no eventee priority groups. This means there are no registered eventees.");
			}
			else
			{
			$this->core->debug(4, "Event \"$category, $eventName\" triggered, but there were no eventee priority groups. This means there are no registered eventees.");
			}
		}
	}
	
	function getKey($category, $eventName, $featureName)
	{
		return md5sum("$category, $eventName, $featureName");
	}
}

$core=core::assert();
$core->registerModule(new Events());
 
?>