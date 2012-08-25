# Installation
## 1) Install the program
For now, the installation method is ./install.sh. I suggest giving it a read to see what it does. I've detailed the different ways of using it below.

### Types of install
#### As root (System-wide)
If you run `./install` as root, mass will be installed to be available system wide.

#### Linked (Development)
If you run `./install linked` as a non-root user, mass will be linked to original the files that it was installed from. This is particularly useful for development. 

##### As yourself (Local)
_Currently broken_

This is currently broken and is low on my priorities to fix. If it's useful to you feel free to have a go.

If you run `./install` as a non-root user, mass will only be available to that user. Realistically I think the only time you'll use this now is experimentation or trying to reproduce a bug.

## 2) Getting data to use
### Hosts
It's important to remember that these sources should only be used periodically and then stored. See sourcesOfData.md for more information.

Here are the sources available right now:

Here are the sources I plan to have available --help=import:

* AWS --help=AWS
* /etc/hosts --help=Hosts --importFromHostsFile
* .ssh/config # TODO
* /etc/ssh/ssh_config # TODO

### Templates
As of this writing, there are two templates. When ever there are more, they will be made available in the templates-available folder. If you'd like to make your own, see creatingATemplate.md.

# Getting started with mass

See gettingStarted.md in the mean time do `mass --help` which will display the features that are most likely useful to someone starting out with mass.

# Updating
Simply do a git pull where ever you checked out the code then run `./install.sh` in the same way you did under the install section.

# Stuff to think about after installing
## What stuff to enable
There are two reasons to consider what you want to enable:

1. Everything that is enabled is using memory all the time and takes time to load. Right now things are sufficiently small that this isn't an issue, but it's not hard to imagine this growing to a size where it's worth taking this into account.
2. In the future there may be alternate versions of the same functionality. Often this functionality will be mutually exclusive, so one will have to be chosen over the other. When this becomes relevant, I'll try to make it as painless as possible.

### Enabling/disabling stuff
This is as simple as creating a symlink from the appropriate -available/fileName to the appropriate -enabled folder. See *Paths explained futher* below.

### Macros
*For now a default install should be fine.*

### Templates
*For now a default install should be fine.*

### Modules
*For now a default install should be fine.*

## Enabling or disabling stuff
It all works like available/enabled system that ubuntu uses via symlinks. The biggest differnce is that enabled folders now sit with profiles/profileName, where each profile is for diffenent interfaces/use-cases (most people will want profiles/commandLine.) You can use `ln -s` to create symlinks in the same way that you'd use cp to copy a file. Please do not simply copy the files as that will make things very hard to diagnose when there are problems.

Here's what my macros folder looks like right now:

    ksandom@lappyg:~/.mass/profiles/commandLine/macros$ ls -l
    total 8
    lrwxrwxrwx 1 ksandom ksandom 62 Aug 25 20:31 createDefaultValues.macro -> /home/ksandom/.mass/macros-available/createDefaultValues.macro
    lrwxrwxrwx 1 ksandom ksandom 47 Aug 25 20:31 from.macro -> /home/ksandom/.mass/macros-available/from.macro
    lrwxrwxrwx 1 ksandom ksandom 54 Aug 25 20:31 getLastList.macro -> /home/ksandom/.mass/macros-available/getLastList.macro
    lrwxrwxrwx 1 ksandom ksandom 48 Aug 25 20:31 hosts.macro -> /home/ksandom/.mass/macros-available/hosts.macro
    lrwxrwxrwx 1 ksandom ksandom 54 Aug 25 20:31 importHosts.macro -> /home/ksandom/.mass/macros-available/importHosts.macro
    lrwxrwxrwx 1 ksandom ksandom 62 Aug 25 20:31 importOldStyleHosts.macro -> /home/ksandom/.mass/macros-available/importOldStyleHosts.macro
    lrwxrwxrwx 1 ksandom ksandom 47 Aug 25 20:31 list.macro -> /home/ksandom/.mass/macros-available/list.macro
    lrwxrwxrwx 1 ksandom ksandom 51 Aug 25 20:31 listterm.macro -> /home/ksandom/.mass/macros-available/listterm.macro
    lrwxrwxrwx 1 ksandom ksandom 56 Aug 25 20:31 macroLoopTest.macro -> /home/ksandom/.mass/macros-available/macroLoopTest.macro
    lrwxrwxrwx 1 ksandom ksandom 47 Aug 25 20:31 noBG.macro -> /home/ksandom/.mass/macros-available/noBG.macro
    lrwxrwxrwx 1 ksandom ksandom 46 Aug 25 20:31 run.macro -> /home/ksandom/.mass/macros-available/run.macro
    lrwxrwxrwx 1 ksandom ksandom 53 Aug 25 20:31 saveToJson.macro -> /home/ksandom/.mass/macros-available/saveToJson.macro
    lrwxrwxrwx 1 ksandom ksandom 52 Aug 25 20:31 testAlias.macro -> /home/ksandom/.mass/macros-available/testAlias.macro
    lrwxrwxrwx 1 ksandom ksandom 49 Aug 25 20:31 testIf.macro -> /home/ksandom/.mass/macros-available/testIf.macro
    lrwxrwxrwx 1 ksandom ksandom 45 Aug 25 20:31 to.macro -> /home/ksandom/.mass/macros-available/to.macro

Notice companyName in the last entry. That is how I'm separating out company specific stuff that should not be shared. See "Adding custom stuff" below.

## Adding custom stuff
Where ever possible, it would be great if you could share the marvelous things you come up with. The reality of this type of program is that it will most often be used in businesses and there will be some company specific things that are confidentuial and shouldn't be shared. In that case, the "Private" section below will be your guide.

### What to create?
Almost always, creatig a macro and/or template is the way to go. There is a lot of functionality available now, and that should be enough to do most things. Where it's not, create a module. Specifically:

 * A macro brings together internal functionality and other macros.
 * A template displays the output of a macro. That output can from from normal macros or macros embedded in the template. This is useful for creating configuration files, particularly where there are sections of non-gererated content, or if the content has more than one form.
 * Modules create internal functionality. Sometimes this is the tidiest way to do it.

### Detailed howtos
For information about how to create macros, templates & modules, see one of these guides below:

 * creatingAMacro.md
 * creatingATemplate.md
 * creatingAModule.md

### Shareable
I suggest doing a linked install for development as it allows you to quickly test things while easily keeping git up-to-date. 

Save your work in the relevant one of the -available folders and create a symlink to it from the relevant -enabled folder.

### Private
I suggest doing a linked install as you will almost definitely have some stuff that you can contribute back. For what you can't, create your work in a separate location (that you will probably want in your own source control) and then symlink it to profiles/commandLine/companyName. Here's an example (written in full):

    cd ~/.mass/profiles/commandLine/packages
    ln -sf ~/work/repos/companyRepo/stuffForMass companyName
    
### Paths explained futher
Mass can be installed system wide, for a particular user, or linked to the checked out repository.

#### System wide
Right now, everything goes into /etc/mass. 

TODO In the furture I intend to separate this out so that only configuration goes in /etc/mass. Anything else that appears there will simply be symlinks to the actual content. As far as the program is concerned, the content is relative to /etc/mass.

NOTE You may want to set permissions of various folders/files so that specific users can administor mass without root access.

TODO Make the permissions part of the default install via a group called mass which users can become a member of.

#### User
This is currently borked and is low on my priorities to fix. The idea is to install mass into ~/.mass and then symlink the mass file into ~/bin

The paths are all the same as the Linked install described below.

#### Linked
For this section, the ~/.mass folder is referred to as _MASS_ and the checked out repository will be refered to as _REPO_.

~/bin/mass (/usr/local/bin/mass on the mac) is a symlink to REPO/mass.

TODO fix paths for the mac so that individual users can install mass on separate accounts.

 * MASS/*-available folders are symlinks to REPO/*-available
 * MASS/*-enabled folders are real folders that contain symlinks to the files in the MASS/*-available/
 * MASS/docs links to REPO/docs
 * MASS/examples links to REPO/examples
 * MASS/core.php links to REPO/core.php
 * MASS/bin is real, but empty... TODO Is this needed? Perhaps the ~/bin/mass should point to inside here...?
 * MASS/config is real. Everything in here is either unique to you or derived on install.
 * MASS/data is real. Everything in here is either unique to you or derived on install.
