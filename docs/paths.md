# Intro to paths
Mass can be installed system wide, for a particular user, or linked to the checked out repository. The structure changes a little based on which way you choose, but most of it stays the same.

For this document, _MASS_ refers to where mass is installed, and the checked out repository will be refered to as _REPO_.

# The paths

~/bin/mass (/usr/local/bin/mass on the mac) is a symlink to REPO/mass.

 * _MASS_/proilfes - You will find the -enabled folders in here.
 * _MASS_/profiles/commandLine - The profile for the command line interface.
 * _MASS_/profiles/commandLine/* - The variout -enabled directories. Note that -enabled is now omitted and therefore assumed.
 * _MASS_/docs links to REPO/docs
 * _MASS_/repos - The mass repo and anythird party repos go here. This is where packages etc are enabled from.
 * _MASS_/repos/mass - The mass repo.
 * _MASS_/examples links to REPO/examples
 * _MASS_/supplimentary - (coming soon) Various stuff that is not part of mass, but belongs with mass. Currently I'm using this for scripts that retrieve external dependancies.
 * _MASS_/externalLibraries - Where external dependancies go that are not part of mass, but really useful for it.
 * _MASS_/core.php links to REPO/core.php
 * _MASS_/config - is real. Everything in here is either unique to you or derived on install.
 * _MASS_/data - is real. Everything in here is either unique to you or derived on install.
 * _MASS_/index.php - Used for the web API
 * _MASS_/interfaces - Libraries for specific interfaces. These go here if they should not be shared with other interfaces.
 * _MASS_/obsolete - This appears if you upgrade from a version of mass that uses an obsolete layout. It's sole purpose is to keep hold of non-standard configuration you may have installed that would have otherwise been destroyed in the upgrade process. If there is nothing in here that you need, it is safe to delete.


# System wide

 * _MASS_ is /etc/mass
 * _MASS_/*-available folders are the various things that can be enabled or disabled.

Right now, everything goes into /etc/mass. 

TODO In the furture I intend to separate this out so that only configuration goes in /etc/mass. Anything else that appears there will simply be symlinks to the actual content. As far as the program is concerned, the content is relative to /etc/mass.

NOTE You may want to set permissions of various folders/files so that specific users can administor mass without root access.

TODO Make the permissions part of the default install via a group called mass which users can become a member of.

# User

 * _MASS_ is ~/.mass
 * _MASS_/*-available folders are the various things that can be enabled or disabled.

This is currently borked and is low on my priorities to fix. The idea is to install mass into ~/.mass and then symlink the mass file into ~/bin

The paths are all the same as the Linked install described below.

# Linked

 * _MASS_ is ~/.mass
 * _MASS_/*-available folders are the various things that can be enabled or disabled and are symlinks to REPO/*-available

TODO fix paths for the mac so that individual users can install mass on separate accounts.

