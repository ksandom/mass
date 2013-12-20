<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.
 
define('programName', 'mass');
$description="A commandline tool/API for doing awesome stuff on many nodes of a cluster.";
$profile='commandLine';

define('configDir', '~%configDir%~');
define('storageDir', '~%storageDir%~');

include "$configDir/core.php";

# initiate core
$core=core::assert();
$core->set('General', 'EOL', '<BR>');
$core->set('General', 'configDir', configDir);
$core->set('General', 'storageDir', storageDir);
$core->set('General', 'profile', $profile);
$core->set('General', 'hostsDir', storageDir.'/data/1LayerHosts');
$core->set('General', 'programName', programName);
$core->set('General', 'description', $description);
include (configDir.'/interfaces/basicWeb.php');
$core->setRef('BasicWeb', 'arguments', $_REQUEST);
loadModules($core, "$configDir/profiles/$profile/modules");
$core->callFeature("registerForEvent", "Mass,finishLate,outNow");

$core->go();

?>
