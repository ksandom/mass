<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class Help extends Module
{
	private $track=null;
	private $store=null;
	private $codes=false;
	
	function __construct()
	{
		parent::__construct('Help');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('oldHelp'), 'oldHelp', 'Deprecated. Display this help. --oldHelp[=searchForTag]', array('deprecated'));
				$this->core->registerFeature($this, array('searchHelp'), 'searchHelp', 'Search tags for help. Will return an array that can be used in a template. --searchHelp[=searchForTag] eg --help=debug .', array('help'));
				$this->core->registerFeature($this, array('getTags'), 'getTags', 'List available tags', array('help'));
				
				$this->core->setRef('General', 'outputObject', $this);
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'oldHelp':
				$this->showHelp($this->core->get('Global', $event));
				break;
			case 'searchHelp':
				return $this->searchHelp($this->core->get('Global', $event));
				break;
			case 'searchAllHelp':
				return $this->searchHelp($this->core->get('Global', $event), true);
				break;
			case 'getTags':
				return $this->getTags($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function searchHelp($tags)
	{
		$output=array();
		$this->store=$this->core->getStore();
		
		if ($tags) $tagsArray=$this->core->interpretParms($tags);
		else $tagsArray=array('user');
		
		foreach ($tagsArray as $tag)
		{
			if (isset($this->store['Tags'][$tag]))
			{
				foreach ($this->store['Tags'][$tag] as $name)
				{
					if (isset($this->store['Features'][$name]))
					{
						$details=$this->store['Features'][$name];
						$details['name']=$name;
						$details['matchedTag']=$tag;
						$details['category']=$details['obj']->getName();
						
						
						$visualFlags=array();
						foreach ($details['flags'] as $flag)
						{
							$visualFlags[]=(strlen($flag)==1)?"-$flag":"--$flag";
						}
						$details['commandLineFlags']=implode(', ', $visualFlags);
						
						$output[]=$details;
					}
					else
					{
						$this->core->debug(0, "searchHelp: Could not find a feature with the name \"$name\". This can happen if the primary flag for a feature is not in the list of flags for that feature.");
					}
				}
			}
			else $this->core->complain($this, "Couldn't find tag.", $tag);
		}
		
		return $output;
	}
	
	function getTags()
	{
		$this->store=$this->core->getStore();
		return array_keys($this->store['Tags']);
	}
	
	function showHelp($tags)
	{ // Deprecated
		$this->track=array();
		$this->store=$this->core->getStore();
		$this->assertCodes();
		
		if ($tags) $this->showSpecificHelp($tags);
		#else $this->showAllHelp();
		else 
		{
			$this->showSpecificHelp('user');
			$allTags=implode(', ', array_keys($this->store['Tags']));
			echo "\n\n{$this->codes['default']}Showing tags for \"{$this->codes['brightWhite']}user{$this->codes['default']}\". \n{$this->codes['green']}Available tags: {$this->codes['default']}$allTags{$this->codes['default']}\n";
		}
	}
	
	function showSpecificHelp($tags)
	{ // Deprecated
		$tagsArray=$this->core->interpretParms($tags);
		foreach ($tagsArray as $tag)
		{
			if (isset($this->store['Tags'][$tag]))
			{
				foreach ($this->store['Tags'][$tag] as $name)
				{
					$details=$this->store['Features'][$name];
					$this->displayHelpItem($name, $details);
				}
			}
			else $this->core->complain($this, "Couldn't find tag.", $tag);
		}
	}
	
	function showAllHelp()
	{ // Deprecated
		$store=$this->core->getStore();
		$track=array();
		
		$programName=$this->core->get('General', 'programName');
		$description=$this->core->get('General', 'description');
		echo "Help\n----\n$programName: $description\n\n";
		foreach ($this->store['Features'] as $name=>$details)
		{
			$this->displayHelpItem($name, $details);
		}
	}
	
	function displayHelpItem($name, $details)
	{ // Deprecated
		if (!isset($this->track[$details['flags'][0]]))
		{
			$this->track[$details['flags'][0]]=true; # Make sure aliases don't cause us to display the same help twice.
			$visualFlags=array();
			foreach ($details['flags'] as $flag)
			{
				$visualFlags[]=(strlen($flag)==1)?"-$flag":"--$flag";
			}
			
			$finalVisualFlags=implode(', ', $visualFlags);
			$objName=$details['obj']->getName();
			
			$this->assertCodes();
			echo "{$this->codes['brightBlack']}$objName: {$this->codes['cyan']}$finalVisualFlags {$this->codes['brightBlack']}=> {$this->codes['default']}{$details['description']}{$this->codes['brightBlack']} ~ {$this->codes['green']}{$details['tagString']}{$this->codes['default']}\n";
		}
	}
	
	function assertCodes()
	{
		if (!$this->codes)
		{
			$this->codes=$this->core->getCategoryModule('Color');
			if (!isset($this->codes['default'])) $this->codes['default']='';
		}
	}
	
	function out($output, $indent='', $prefix=false)
	{
		if ($this->core->get('General', 'outputStyle')=='printr')
		{
			print_r($output);
		}
		else
		{
			$this->assertCodes();
			
			$derivedPrefix=($prefix or is_numeric($prefix))?"$prefix{$this->codes['default']}: ":'';
			if (is_string($output)) 
			{
				echo "$indent{$this->codes['green']}$derivedPrefix$output\n";
			}
			elseif (is_array($output))
			{
				echo "$indent{$this->codes['cyan']}$derivedPrefix\n";
				foreach ($output as $key=>$value)
				{
					$this->out($value, $indent.'  ', "$key");
				}
			}
			elseif (is_null($output))
			{
				if ($prefix)
				{
					echo "$indent{$this->codes['purple']}{$derivedPrefix}NULL\n";
				}
			}
			elseif (is_numeric($output))
			{
				echo "$indent{$this->codes['purple']}{$derivedPrefix}$output\n";
			}
			elseif (is_bool($output))
			{
				$display=($output)?'True':'False';
				echo "$indent{$this->codes['purple']}{$derivedPrefix}$display\n";
			}
			else
			{
				echo "$indent{$this->codes['red']}{$prefix}{$this->codes['default']}: {$this->codes['brightBlack']}I can't display this data type yet.{$this->codes['default']}\n";
			}
			
			echo "{$this->codes['default']}";
		}
	}
}

$core=core::assert();
$core->registerModule(new Help());
 
?>