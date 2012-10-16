<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# TODO WARNING This example is out of date. It needs to be carefully revised. Below is a list of known shortcomings (there may be more), please prepend DONE in front of each item as it gets fixed.
# * Specifying tags
# * Double check init levels (init, followup etc)
# * Any more???

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
				$this->core->registerFeature($this, array('E', 'example', 'aFeature'), 'example', 'An example feature.', array('user', 'string'));
					/*
						Let's explain this one a little further:
						$this
							We're sending a reference to ourselves so that events can be directly triggered while keeping any private variable values correct.
						
						array('E', 'example', aFeature)
							These are the arguments we'll respond to. Single character arguments can be referenced on the command line like so: -E
							While multiple character arguments can be referenced on the command line like so: --example or --aFeature. In practise you probably only want a 1 multiple character argument and possibly 1 single character argument per feature.
							
							You should always include a multiple character argument, but only include a single if the feature is essential and going to be very regularly used by a human.
						
						'example'
							This is the primary argument that will be passed to the event no matter which one was actually used by the user.
						
						'An example feature.'
							The description. This will be used when displaying help about the feature. This should be short. If you need to write an essay, it should go in docs/moduleName.md
							
						array('user', 'string')
							'user' and 'string' are tags to make it easier to find things within help.
					*/
				break;
			case 'followup':
				# Do anything you need to do before the main flow begins, but requires all the other modules to be loaded. IT WILL BE RARE THAT YOU SHOULD USE THIS! An example is commandLine.php which needs all modules to be loaded before it can process arguments that might reference them.
				break;
			case 'last':
				# Do anything you need to do before the main flow begins, but requires all the other modules to be loaded. IT WILL BE RARE THAT YOU SHOULD USE THIS! An example is commandLine.php which needs all modules to be loaded before it can process arguments that might reference them.
				break;
			case 'example':
				# Note that we need the return here. If you find your feature isn't having any effect, check here.
				return $this->example();
				break;
			case 'example2': # TODO finish this example
				# TODO This could be more elegant
				# Handle the parameters.
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', 'example2'));
				# Note the 'example2' in the line above
				
				
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->example2($parms);
				break;
				
			# TODO write example using shared memory
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function example()
	{
		# In this case we're going to return a single string. Note that it is still in an array. This is required. (There may still be some legacy code that doesn't require it. That will be replaced in time.)
		$result=array('stuff');
		
		# You don't have to return something. If you do, what you return will replace the shared memory. If you don't, it will remain intact.
		return $result;
	}
}

$core=core::assert();
$core->registerModule(new Example());
 
?>