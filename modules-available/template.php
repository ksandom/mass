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
				$this->core->registerFeature($this, array('nestTemplates'), 'nestTemplates', "Nest templates. --nestTemplates=rootTemplate,[inputField],[outputField],firstNestedTemplate,[inputField],[outputField][,secondNestedTemplate,[inputField],[outputField][,thirdNestedTemplate,[inputField],[outputField][,etc[,etc]]]] . The syntax works in sets of 3. The first field is the template to use. The second is the field to pass as the input to the next template. If the input is omitted, the whole level will be passed to the next template. The third is the field to put the result from the next template into. If ommitted the whole level will be replaced by a string that can be accessed via ~%line%~ .");
				$this->core->registerFeature($this, array('nestTemplatesOut'), 'nestTemplatesOut', "Same as nestTemplates, but will use the output object instead.");
				
				$this->loadEnabledTenmplates();
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'template':
				#$this->core->setRef('General', 'outputObject', $this);
				return array($this->processTemplateByName($this->core->get('Global', 'template'), $this->core->getResultSet()));
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
			case 'nestTemplates':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 1);
				return array($this->nestTemplates($this->core->getResultSet(), $parms[0], $parms[1], $parms[2], $parms[3]));
				break;
			case 'nestTemplatesOut':
				$this->core->setRef('General', 'outputObject', $this);
				$this->templateOut=$this->core->get('Global', $event);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadEnabledTenmplates()
	{
		$profile=$this->core->get('General', 'profile');
		$templateDir=$this->core->get('General', 'configDir')."/profiles/$profile/templates";
		$list=$this->core->getFileList($templateDir);
		$this->core->addItemsToAnArray('Core', 'templatesToLoad', $list);
	}

	function processTemplateByName($name, $input=false)
	{
		$derivedTemplateName="$name.template";
		$list=$this->core->get('Core', 'templatesToLoad');
		if (isset($list[$derivedTemplateName])) $templateFile=$list[$derivedTemplateName];
		else $templateFile=(file_exists($derivedTemplateName))?$derivedTemplateName:$name;
		
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
			
			$result=$this->core->callFeature($argument, $value);
		}
		
		$finalResult=$this->insertResultIntoTemplate($result, $outputTemplate);
		
		$contents=$before.$finalResult.$after;
		
		return $contents;
	}
	
	function isInsertable($value)
	{
		return (is_string($value) or is_numeric($value));
	}
	
	function insertResultIntoTemplate($input, $template)
	{
		$output='';
		$this->core->debug(3, "insertResultIntoTemplate: Entered");
		if (is_array($input))
		{
			foreach ($input as $key=>$inputLine)
			{
				$this->core->debug(3, "insertResultIntoTemplate: processin key $key");
				$templateLine=$template;
				if (is_array($inputLine))
				{
					foreach ($inputLine as $lineKey=>$lineValue)
					{
						if ($this->isInsertable($lineValue))
						{
							$this->core->debug(4, "Template: itemKey=$key, lineKey=$lineKey");
							$templateLine=implode(strval($lineValue), explode(resultVarBegin."$lineKey".resultVarEnd, $templateLine));
						}
						else $this->core->debug(4, "Template: itemKey=$key, lineKey=$lineKey, inputLine=OBJECT-skipped");
					}
					$templateLine=implode(strval($key), explode(resultVarBegin."key".resultVarEnd, $templateLine));
				}
				else
				{
					$this->core->debug(4, "Template: The line is not an array, inputLine=$inputLine");
					$templateLine=implode(strval($inputLine), explode(resultVarBegin."line".resultVarEnd, $templateLine));
					$templateLine=implode(strval($key), explode(resultVarBegin."key".resultVarEnd, $templateLine));
				}
				$output.=$this->core->processValue($templateLine);
			}
		}
		else $output=$input;
		
		return $output;
	}
	
	function indent($dataIn, $indentCharacter='	')
	{
		if (is_array($dataIn))
		{
			$output=array();
			foreach ($dataIn as $key=>$line)
			{
				$output[$key]=$this->indent($line, $indentCharacter);
			}
			return $output;
		}
		else
		{
			$output='	'.implode("\n	", explode("\n", $dataIn));
			$length=strlen($output);
			if (substr($output, $length-1, 1)=='	') $output=substr($output, 0, $length-1);
			return $output;
		}
	}
	
	function nestTemplates($dataIn, $templateName, $input, $output, $remainder, $autoIndent=true)
	{
		$dataOut=$dataIn;
		if ($remainder)
		{
			$this->core->debug(2, "nestTemplates: Processing remainder $remainder");
			# TODO debug this. Check input and output.
			foreach ($dataIn as $key=>$line)
			{
				if ($input)
				{
					if (isset($line[$input]))
					{
						$outputLine=$this->core->callFeatureWithDataset('nestTemplates', $remainder, $line[$input], $autoIndent);
						$this->core->debug(2, "nestTemplates: Using input $input $remainder");
					}
					else
					{
						$this->core->debug(2, "nestTemplates: Input key $input did not exist when trying to process template the remaining part of nestTemplates sequence $remainder.");
						$outputLine=false;
					}
				}
				else
				{
					$this->core->debug(2, "nestTemplates: Taking the whole array for $remainder");
					$outputLine=$this->core->callFeatureWithDataset('nestTemplates', $remainder, $line);
				}
				
				if ($outputLine!==false)
				{
					$outputLine=$this->indent($outputLine);
					if ($output) $dataOut[$key][$output]=$outputLine[0];
					else $dataOut[$key]=$outputLine[0];
				}
			}
			$this->core->debug(2, "nestTemplates: Finished remainder $remainder");
		}
		
		return $this->processTemplateByName($templateName, $dataOut);
	}
	
	function out($output)
	{
		$modifiedOutput=$this->core->callFeatureWithDataset('triggerEvent', 'Template,beforeProcessing-'.$this->templateOut, $output);
		if ($modifiedOutput) $output=$modifiedOutput;
		
		if (is_string($output)) $this->core->echoOut("template: Unexpected string=\"$output\"");
		elseif(strpos($this->templateOut, ',')!==false)
		{
			$this->core->debug(2, "Template->out: Using nestedTemplates: {$this->templateOut}");
			$result=$this->core->callFeatureWithDataset('nestTemplates', $this->templateOut, $output);
		}
		else
		{
			$this->core->debug(2, "Template->out: Using a single template: {$this->templateOut}");
			$result=array($this->processTemplateByName($this->templateOut, $output));
		}
		
		$modifiedResult=$this->core->callFeatureWithDataset('triggerEvent', 'Template,beforeOutput-'.$this->templateOut, $result);
		if ($modifiedResult) $this->core->echoOut($modifiedResult[0]);
		else $this->core->echoOut($result[0]);
	}
	
	function put($output)
	{
		echo $output;
	}
}

$core=core::assert();
$core->registerModule(new Template());
 
?>