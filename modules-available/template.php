<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Takes a template and outputs stuff based on queries within the template

define ('templateMacroBegin', '<~');
define ('templateOnlyBegin', '<~~');
define ('templateMacroEnd', '~>');
define ('templateMacroTransition', '~~');


class Template extends Module
{
	private $templateOut=false;
	
	function __construct()
	{
		parent::__construct('Template');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('template'), 'template', 'Specify a templte to use to display the output. This will replace the result with an array containing a single string. --template=templateName . eg --template=screen');
				$this->core->registerFeature($this, array('templateOut'), 'templateOut', 'Use a template to display the output before '.programName.' terminates. --templateOut=templateName . eg --templateOut=screen');
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'template':
				#$this->core->setRef('General', 'outputObject', $this);
				return array($this->processTemplateByName($this->core->get('Global', 'template'), $this->core->getSharedMemory()));
				break;
			case 'templateOut':
				$this->core->setRef('General', 'outputObject', $this);
				$this->templateOut=$this->core->get('Global', 'templateOut');
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function processTemplateByName($name, $input=false)
	{
		$templateDir=$this->core->get('General', 'configDir').'/templates-enabled';
		$derivedTemplateName="$templateDir/$name.template";
		$templateFile=(file_exists($derivedTemplateName))?$derivedTemplateName:$name;
		return $this->processTemplate($templateFile, $input);
	}
	
	function processTemplate($fileName, $input=false)
	{
		if (file_exists($fileName))
		{
			$contents=file_get_contents($fileName);
			
			while (strpos($contents, templateMacroBegin)!==false)
			{
				$contents=$this->findAndRunMacro($contents, $input);
			}
			
			return $contents;
		}
		else $this->core->complain($this, "Could not find file $fileName");
	}
	
	function findAndRunMacro($contents, $input=false)
	{
		$begin=strpos($contents, templateMacroBegin);
		$beginTemplateOnly=strpos($contents, templateOnlyBegin);
		$end=strpos($contents, templateMacroEnd);
		$transition=strpos($contents, templateMacroTransition);
		
		$before=substr($contents, 0, $begin);
		$after=substr($contents, $end+strlen(templateMacroEnd));
		
		$macroLength=$end-$begin-strlen(templateMacroBegin)-strlen(templateMacroEnd);
		$macro=substr($contents, $begin+strlen(templateMacroBegin)+1, $macroLength);
		
		if ($begin==$beginTemplateOnly)
		{ // Just take input from the resultset
			$macroCode='';
			$outputTemplate=substr($macro, 1);
			
			$result=$input;
		}
		else
		{ // Traditional embedded macro
			$parts=explode(templateMacroTransition, $macro);
			
			$macroCode=$parts[0];
			$outputTemplate=$parts[1];
			
			$argumentTerminatorPos=strpos($macroCode, ' ');
			$argument=substr($macroCode, 0, $argumentTerminatorPos);
			$value=substr($macroCode, $argumentTerminatorPos+1);
			
			$result=$this->core->triggerEvent($argument, $value);
		}
		
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
		if (is_string($output)) echo "template: Unexpected string=\"$output\"\n";
		else
		{
			echo $this->processTemplateByName($this->templateOut, $output);
		}
	}
}

$core=core::assert();
$core->registerModule(new Template());
 
?>