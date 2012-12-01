<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Provides terminal codes

class Codes extends Module
{
	function __construct()
	{
		parent::__construct('Codes');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->loadCodes();
				$this->core->registerFeature($this, array('generateColors'), 'generateColors', 'Generate colors.', array('userExtra'));
				#$this->core->registerFeature($this, array('noColor', 'nocolor', 'b'), 'noColor', 'Turn off colored output.', array('userExtra'));

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'generateColors':
				$this->loadColorCodes(true);
				break;
			#case 'noColor':
			#	$this->loadColorCodes(false);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadCodes()
	{
		$this->loadControlCodes();
		# $this->loadColorCodes();
		$this->loadDefaultAliases();
	}
	
	function loadControlCodes()
	{
		$this->core->set('Codes', 'esc', "\e");
		$this->core->set('Codes', '!', "~!");
		$this->core->set('Codes', '!!', "!~");
		$this->core->set('Codes', '%', "~%");
		$this->core->set('Codes', '%%', "%~");
		$this->core->set('Codes', 'break', "\033c");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		#$this->core->set('Codes', '', "");
		
		$this->core->set('Codes', 'testControl', "This shows that the control codes have been loaded.");
	}
	
	function loadColorCodes($useColor=true)
	{
		$shortNamesBelongTo='dark';
		
		/* Useful links
			http://www.termsys.demon.co.uk/vtansi.htm
			http://www.frexx.de/xterm-256-notes/
			http://tldp.org/HOWTO/Bash-Prompt-HOWTO/x329.html
		*/
		
		$deck=array(
			0=>'dark', // Potentially this should be reset....
			1=>'bright',
			#2=>'dim', #potentiall this should replace 0
			4=>'underscore',
			5=>'blink',
			7=>'reverse');
			#8=>'hidden');
			
		$color=array(
			'foreground'=>array(
				30=>'Black',
				31=>'Red',
				32=>'Green',
				33=>'Yellow',
				34=>'Blue',
				35=>'Purple',
				36=>'Cyan',
				37=>'White'),
			'background'=>array(
				40=>'HLBlack',
				41=>'HLRed',
				42=>'HLGreen',
				43=>'HLYellow',
				44=>'HLBlue',
				45=>'HLPurple',
				46=>'HLCyan',
				47=>'HLWhite'));
		
		foreach ($deck as $deckKey=>$deckName)
		{
			foreach ($color as $rangeName=>$range)
			{
				foreach ($range as $colorKey=>$colorName)
				{
					$shortname='';
					$colorCode=($useColor)?"\033[$deckKey;{$colorKey}m":'';
					$this->core->set('Color', "$deckName$colorName", $colorCode);
					
					if ($deckName==$shortNamesBelongTo)
					{ // give short names to the lover deck
						$shortname=strtolower($colorName);
						$this->core->set('Color', $shortname, $colorCode);
					}
					
					if ($rangeName=='foreground')
					{
						foreach ($color['background'] as $bgColorKey=>$bgColorName)
						{
							$bgColorCode=($useColor)?"\033[$deckKey;{$colorKey};{$bgColorKey}m":'';
							$withBGKey=($shortname)?"$shortname$bgColorName":"$deckName$colorName$bgColorName";
							
							// Filter out combinations that are likely to be very hard to read.
							if ( (($colorKey==$bgColorKey-10 and $deskName='dark'))
								or ($deckName=='bright' and $bgColorKey==37)
								)
							{
								$this->core->set('RejectedColor', "$withBGKey", $bgColorCode);
							}
							else
							{
								$this->core->set('Color', "$withBGKey", $bgColorCode);
							}
							#$this->core->debug(0, "d=$deckKey bg=$bgColorKey fg=$colorKey");
						}
					}
				}
			}
		}
		
		$colorCode=($useColor)?"\033[0;0m":'';
		$this->core->set('Color', 'default', $colorCode);
		
		$this->core->set('Color', 'testColor', "{$colorCode}This shows that the color codes have been loaded.");
	}
	
	function loadDefaultAliases()
	{
		$this->core->set('Codes', 'debug0', $this->core->get('Color', 'brightBlack'));
		$this->core->set('Codes', 'debug1', $this->core->get('Color', 'brightRed'));
		$this->core->set('Codes', 'debug2', $this->core->get('Color', 'red'));
		$this->core->set('Codes', 'debug3', $this->core->get('Color', 'yellow'));
		$this->core->set('Codes', 'debug4', $this->core->get('Color', 'green'));
		$this->core->set('Codes', 'debug5', $this->core->get('Color', 'cyan'));
		
		$this->core->set('Codes', 'debug6', $this->core->get('Color', 'brightBlue'));
		$this->core->set('Codes', 'debug7', $this->core->get('Color', 'brightBlue'));
		$this->core->set('Codes', 'debug8', $this->core->get('Color', 'brightBlue'));
		$this->core->set('Codes', 'debug9', $this->core->get('Color', 'brightBlue'));
		$this->core->set('Codes', 'default', $this->core->get('Color', 'default'));
	}
}

$core=core::assert();
$core->registerModule(new Codes());
 
?>