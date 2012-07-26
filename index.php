<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.
 
define('programName', 'mass');
$description="A commandline tool/API for doing awesome stuff on many nodes of a cluster.";

$configDir=getcwd();

include "$configDir/core.php";

# initiate core
$core=core::assert();
$core->set('General', 'configDir', $configDir);
$core->set('General', 'hostsDir', "$configDir/data/1LayerHosts");
$core->set('General', 'programName', programName);
$core->set('General', 'description', $description);
include ($configDir.'/interfaces/basicWeb.php');
$core->setRef('BasicWeb', 'arguments', $_REQUEST);
loadModules($core, "$configDir/modules-enabled");

$core->go();

?>
