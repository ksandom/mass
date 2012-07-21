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
				$this->core->registerFeature($this, array('noTemplateOut'), 'noTemplateOut', 'Do not allow futue --templateOutIfNotSet to be set. It will not have effect if one has already been set.');
				$this->core->registerFeature($this, array('unsetTemplateOut'), 'unsetTemplateOut', 'Unset the current templateOut. This disables the output, but allows --templateOutIfNotSet to be used again.');
				$this->core->registerFeature($this, array('templateOutIfNotSet'), 'templateOutIfNotSet', "Same as --templateOut, but will only be set if it hasn't been already.");
				
				$this->loadEnabledTenmplates();
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
				$this->core->debug(4, "--templateOut: set \$this->templateOut to {$this->templateOut}.");
				break;
			case 'noTemplateOut':
				if (!$this->templateOut)
				{
					$this->templateOut=$this->core->get('Global', 'dontset');
					$this->core->debug(4, "Disabled --templateOutIfNotSet");
				}
				break;
			case 'templateOutIfNotSet':
				if (!$this->templateOut)
				{
					$this->core->debug(4, "--templateOutIfNotSet: \$this->templateOut is {$this->templateOut}.");
					$this->core->setRef('General', 'outputObject', $this);
					$this->templateOut=$this->core->get('Global', 'templateOutIfNotSet');
					$this->core->debug(4, "--templateOutIfNotSet: set \$this->templateOut to {$this->templateOut}.");
				}
				else $this->core->debug(4, "--templateOutIfNotSet: \$this->templateOut has already been set.");
				break;
			case 'unsetTemplateOut':
				$this->templateOut=false;
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadEnabledTenmplates()
	{
		$templateDir=$this->core->get('General', 'configDir').'/templates-enabled';
		$list=$this->core->getFileList($templateDir);
		$this->core->addItemsToAnArray('Core', 'templatesToLoad', $list);
	}

	function processTemplateByName($name, $input=false)
	{
		# TODO Double check and tidy this.
		$derivedTemplateName="$name.template";
		$list=$this->core->get('Core', 'templatesToLoad');
		if (isset($list[$derivedTemplateName])) $templateFile=$list[$derivedTemplateName];
		else
		{
			$templateFile=(file_exists($derivedTemplateName))?$derivedTemplateName:$name;
		}
		
		return $this->processTemplate($templateFile, $input);
	}
	
	function processTemplate($fileName, $input=false)
	{
		if ($fileName)
		{
			if (file_exists($fileName))
			{
				$contents=file_get_contents($fileName);
				
				while (strpos($contents, templateMacroBegin)!==false)
				{
					$contents=$this->findAndRunMacro($contents, $input);
				}
				
				return $this->core->processValue($contents);
			}
			else $this->core->complain($this, "Could not find file $fileName");
		}
		else
		{
			$this->core->debug(1, "processTemplate: fileName was empty. This is probably intentinoal. If not, check that the variable has been resolved as expected.");
		}
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
		if (is_array($input))
		{
			foreach ($input as $inputLine)
			{
				$templateLine=$template;
				if (is_array($inputLine))
				{
					foreach ($inputLine as $lineKey=>$lineValue)
					{
						if (!is_object($lineValue))
						{
						$this->core->debug(4, "Template: lineKey=$lineKey, inputLine=$inputLine");
						$templateLine=implode(strval($lineValue), explode(resultVarBegin."$lineKey".resultVarEnd, $templateLine));
						}
						else $this->core->debug(4, "Template: lineKey=$lineKey, inputLine=OBJECT-skipped");
					}
				}
				else
				{
					$this->core->debug(4, "Template: The line is not an array, inputLine=$inputLine");
					$templateLine=implode(strval($inputLine), explode(resultVarBegin."line".resultVarEnd, $templateLine));
				}
				$output.=$this->core->processValue($templateLine);
			}
		}
		else $output=$input;
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