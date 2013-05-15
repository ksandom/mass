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
				$this->core->registerFeature($this, array('registerOnceForEvent', 'registerForEvent'), 'registerOnceForEvent', "Register a feature once to be executed when a particular event is triggered. --registerForEvent=Category,eventName,featureName[,featureValue]", array());
				$this->core->registerFeature($this, array('registerMultipleTimesForEvent'), 'registerMultipleTimesForEvent', "Register a feature to be executed when a particular event is triggered. If you use --registerMultipleTimesForEvent multiple times for a single action (say saving some data), then that action will get called once for everytime it was registered per trigger of that event. This is probably not what you want. Usually --registerOnceForEvent (--registerForEvent) will be what you want. --registerEvent=Category,eventName,featureName[,featureValue]", array());
				$this->core->registerFeature($this, array('triggerEvent'), 'triggerEvent', "Trigger an event. --triggerEvent=Category,eventName[,value] . Note that if value is given, it will be appended as an extra option to what ever was provided when the eventee was registered.", array());
				break;
			case 'followup':
				$this->triggerEvent('Startup', 'followup');
				break;
			case 'last':
				$this->triggerEvent('Startup', 'last');
				break;
			case 'registerMultipleTimesForEvent':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 3, true);
				$this->registerForEvent($parms[0], $parms[1], $parms[2], $parms[3]);
				break;
			case 'registerOnceForEvent':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 3, true);
				$this->registerForEvent($parms[0], $parms[1], $parms[2], $parms[3], true);
				break;
			case 'triggerEvent':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				return $this->triggerEvent($parms[0], $parms[1], $parms[2]);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function registerForEvent($category, $eventName, $featureName, $featureValue='', $onlyOnce=false, $priority=50)
	{
		$priorityGroups=$this->core->get('Events', "$category-$eventName");
		if (!isset($priorityGroups[$priority])) $priorityGroups[$priority]=array();
		
		$newValue=array('featureName'=>$featureName, 'featureValue'=>$featureValue);
		if ($onlyOnce) $priorityGroups[$priority][md5("$featureName,$featureValue")]=$newValue;
		else $priorityGroups[$priority][]=$newValue;
		
		$this->core->debug(3, "Registered \"$featureName $featureValue\" to event \"$category, $eventName\" at priority $priority.");
		$this->core->set('Events', "$category-$eventName", $priorityGroups);
	}
	
	
	function unRegisterEvent($category, $eventName, $featureName)
	{
		# TODO write this. It will be useful for unloading code.
	}
	
	function setPriority($category, $eventName, $featureName, $priority=50)
	{
		# TODO Write this. If this becomes relied on a lot, check to see if tasks should actually be part of macros. I envisage priorities being used when something HAS to be done first or last. Eg preparing folders for downloads, or cleaning up afterwards.
	}
	
	function triggerEvent($category, $eventName, $value='')
	{
		$this->core->debug(4, "triggerEvent: $category,$eventName");
		$priorityGroups=$this->core->get('Events', "$category-$eventName");
		if (is_array($priorityGroups) && count($priorityGroups)>0)
		{
			foreach ($priorityGroups as $priority=>$priorityGroup)
			{
				if (count($priorityGroup))
				{
					$nesting=$this->core->incrementNesting();
					
					foreach ($priorityGroup as $eventee)
					{
						$this->core->debug(3, "triggerEvent: $category,$eventName: --{$eventee['featureName']}={$eventee['featureValue']}");
						
						if ($value!='') $valueToSend=($eventee['featureValue'])?$eventee['featureValue'].','.$value:$value;
						else $valueToSend=$eventee['featureValue'];
						
						$result=$this->core->callFeature($eventee['featureName'], $valueToSend);
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
					$this->core->doUnSet('Events', "$category-$eventName");
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