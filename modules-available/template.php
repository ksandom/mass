<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Takes a template and outputs stuff based on queries within the template

define ('templateMacroBegin', '<~');
define ('templateMacroEnd', '~>');
define ('templateMacroTransition', '~~');


class Template extends Module
{
	function __construct()
	{
		parent::__construct('Template');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('template'), 'template', 'Specify a file to use as a template.');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'template':
				#$this->core->setRef('General', 'outputObject', $this);
				return array($this->processTemplateByName($this->core->get('Global', 'template')));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function processTemplateByName($name)
	{
		$templateDir=$this->core->get('General', 'configDir').'/templates-enabled';
		$derivedTemplateName="$templateDir/$name.template";
		$templateFile=(file_exists($derivedTemplateName))?$derivedTemplateName:$name;
		return $this->processTemplate($templateFile);
	}
	
	function processTemplate($fileName)
	{
		if (file_exists($fileName))
		{
			$contents=file_get_contents($fileName);
			
			while (strpos($contents, templateMacroBegin)!==false)
			{
				$contents=$this->findAndRunMacro($contents);
			}
			
			return $contents;
		}
		else $this->core->complain($this, "Could not find file $fileName");
	}
	
	function findAndRunMacro($contents)
	{
		$begin=strpos($contents, templateMacroBegin);
		$end=strpos($contents, templateMacroEnd);
		$transition=strpos($contents, templateMacroTransition);
		
		$before=substr($contents, 0, $begin);
		$after=substr($contents, $end+strlen(templateMacroEnd));
		
		$macroLength=$end-$begin-strlen(templateMacroBegin)-strlen(templateMacroEnd);
		$macro=substr($contents, $begin+strlen(templateMacroBegin)+1, $macroLength);
		
		$parts=explode(templateMacroTransition, $macro);
		
		$macroCode=$parts[0];
		$outputTemplate=$parts[1];
		
		$argumentTerminatorPos=strpos($macroCode, ' ');
		$argument=substr($macroCode, 0, $argumentTerminatorPos);
		$value=substr($macroCode, $argumentTerminatorPos+1);
		
		#echo "blah1\n";
		#print_r($this->core->getSharedMemory());
		#$this->core->makeParentShareMemoryCurrent();
		#print_r($this->core->getSharedMemory());
		#echo "blah2\n";
		$result=$this->core->triggerEvent($argument, $value);
		$finalResult=$this->insertResultIntoTemplate($result, $outputTemplate);
		
		$contents=$before.$finalResult.$after;
		
		return $contents;
	}
	
	function insertResultIntoTemplate($input, $template)
	{
		$output='';
		foreach ($input as $inputLine)
		{
			$templateLine=$template;
			foreach ($inputLine as $lineKey=>$lineValue)
			{
				$templateLine=implode(strval($lineValue), explode("%$lineKey%", $templateLine));
			}
			$output.=$templateLine;
		}
		return $output;
	}
	
	function out($output)
	{
		echo "template. This module isn't designed to be used this way. So if you reading this, something went wrong. Here's the output: $output";
	}
}

$core=core::assert();
$core->registerModule(new Template());
 
?>