<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# An example of how to write a module

class Example extends Module
{
	function __construct()
	{
		parent::__construct('Example');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				# Declare all your functionality here.
				$this->core->registerFeature($this, array('e', 'example', 'aFeature'), 'example', 'An example feature.');
					/*
						Let's explain this one a little further:
						$this
							We're sending a reference to ourselves so that events can be directly triggered while keeping any private variable values correct.
						
						array('e', 'example', aFeature)
							These are the arguments we'll respond to. Single character arguments can be referenced on the command line like so: -e
							While multiple character arguments can be referenced on the command line like so: --example or --aFeature. In practise you probably only want a 1 multiple character argument and possibly 1 single character argument per feature.
							
							You should always include a multiple character argument, but only include a single if the feature is essential and going to be very regularly used by a human.
						
						'example'
							This is the primary argument that will be passed to the event no matter which one was actually used by the user.
						
						'An example feature.'
							The description. This will be used when displaying help about the feature. This should be short. If you need to write an essay, it should go in docs/moduleName.md
					*/
				break;
			case 'followup':
				# Do anything you need to do before the main flow begins, but requires all the other modules to be loaded. IT WILL BE RARE THAT YOU SHOULD USE THIS! An example is commandLine.php which needs all modules to be loaded before it can process arguments that might reference them.
				break;
			case 'example':
				return $this->aFeature();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function aFeature()
	{
		$result=array('stuff');
		return $result;
	}
}

$core=core::assert();
$core->registerModule(new Example());
 
?>