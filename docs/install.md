# Introduction

First off, thanks for installing mass. I hope you like it.

Throughout this documentation _MASS_ represents where mass is installed (most likely ~/.mass of /etc/mass), and _REPO_ represents where the repository was checked out to. See [paths.md](paths.md) for more information.

Once installed, `mass --help=searchTerm` is your friend!

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

* [AWS](../packages-available/AWS/docs/importingHostsFromAWS.md) --help=AWS
* /etc/hosts --help=Hosts --importFromHostsFile
* .ssh/config # TODO
* /etc/ssh/ssh_config # TODO

### Other stuff

Currently everything else that you need is generated for you during install.

# Getting started with mass

See [gettingStarted.md](gettingStarted.md) in the mean time do `mass --help` which will display the features that are most likely useful to someone starting out with mass. You can also do:

 * `mass --help=thing` which will search all help for entries containing "thing". 
 * `mass --help=all` which will show eeeeeeeeeeeeeeverything. This is now getting sufficiently long that it's more about showing off "Hey! We haz all da stuff!"

# Updating
Simply do a git pull where ever you checked out the code then run `./install.sh` in the same way you did under the install section.

The `./install.sh` is important since I'm regularly refactoring the internals at the moment, so the install will sort that out.

# Stuff to think about after installing
## What stuff to enable
There are two reasons to consider what you want to enable:

1. Everything that is enabled is using memory all the time and takes time to load. Right now things are sufficiently small that this isn't an issue, but it's not hard to imagine this growing to a size where it's worth taking this into account.
2. In the future there may be alternate versions of the same functionality. Often this functionality will be mutually exclusive, so one will have to be chosen over the other. When this becomes relevant, I'll try to make it as painless as possible.
3. Security. You can serve mass as an API via apache or nginx. **At this time, you can't run this accidentally, so you don't need to panic yet.** If you do this, you want to think **very** carefully about what you want people to have access to, and more importantly what you don't want them to have access to. I strongly recommend locking down access to the API also. Where I work, we only have it accessible from inside any given environment. Read more [here](runningMassAsAnAPI.md).

## Enabling or disabling stuff
_I've gone to a lot of effort to make the defaults pretty good. Feed back is welcome._

It all works like available/enabled system that ubuntu uses via symlinks. The biggest differnce is that enabled folders now sit with profiles/profileName, where each profile is for diffenent interfaces/use-cases (most people will want profiles/commandLine.) You can use `ln -s` to create symlinks in the same way that you'd use cp to copy a file. Please do not simply copy the files as that will make things very hard to diagnose when there are problems.

TODO This needs updating.

Here's what my packages folder looks like right now:

ksandom@lappyg:~/.mass/profiles/commandLine/packages$ ls -l
total 11
    lrwxrwxrwx 1 ksandom ksandom 53 Dec 14 18:56 AWS -> /home/ksandom/.mass/repos/mass/packages-available/AWS
    lrwxrwxrwx 1 ksandom ksandom 55 Dec 14 18:56 Codes -> /home/ksandom/.mass/repos/mass/packages-available/Codes
    lrwxrwxrwx 1 ksandom ksandom 61 Dec 14 18:56 DetectStuff -> /home/ksandom/.mass/repos/mass/packages-available/DetectStuff
    lrwxrwxrwx 1 ksandom ksandom 56 Dec 14 18:56 Events -> /home/ksandom/.mass/repos/mass/packages-available/Events
    lrwxrwxrwx 1 ksandom ksandom 53 Dec 14 18:56 Get -> /home/ksandom/.mass/repos/mass/packages-available/Get
    lrwxrwxrwx 1 ksandom ksandom 94 Dec 14 18:56 hailo -> /home/ksandom/files/work/hailo/repos/puppet-live/modules/mass/files/hailoSpecific/hailoPackage
    lrwxrwxrwx 1 ksandom ksandom 54 Dec 14 18:56 Help -> /home/ksandom/.mass/repos/mass/packages-available/Help
    lrwxrwxrwx 1 ksandom ksandom 55 Dec 14 18:56 Hosts -> /home/ksandom/.mass/repos/mass/packages-available/Hosts
    lrwxrwxrwx 1 ksandom ksandom 57 Dec 14 18:56 Install -> /home/ksandom/.mass/repos/mass/packages-available/Install
    lrwxrwxrwx 1 ksandom ksandom 63 Dec 14 18:56 Manipulations -> /home/ksandom/.mass/repos/mass/packages-available/Manipulations
    lrwxrwxrwx 1 ksandom ksandom 59 Dec 14 18:56 MiscTests -> /home/ksandom/.mass/repos/mass/packages-available/MiscTests
    lrwxrwxrwx 1 ksandom ksandom 59 Dec 14 18:56 Semantics -> /home/ksandom/.mass/repos/mass/packages-available/Semantics
    lrwxrwxrwx 1 ksandom ksandom 53 Dec 14 18:56 SSH -> /home/ksandom/.mass/repos/mass/packages-available/SSH

This is mostly done with packages now, which makes managing all this much more sane. See [creatingAPacakge.md](creatingAPacakge.md) for details. **If you have stuff you need to write for work, that you can't share, a package linked from outside the repository is the way to do it.**

See [paths.md](paths.md) for more information.

## Creating non-mass scripts that use mass output

This is very much one of the intended uses of mass. Please however, take some care to make sure that you are not relying on a "pretty" interface, since these change over time and would therefore break your script.

There are currently two ways to go about it:

 * use `--toString='~%variableName%~ some text ~%anotherVariableName%~'` if it's a single line per result that you want.
 * [create a template](creatingATemplate.md) that will provide everything you need. If you create other macros/templates, you probably want to create a [package](creatingAPacakge.md).

## 3) Getting help

* For specific details `mass --help` or `mass --help=searchTerm`
* For bigger picture stuff, start with the documentation:
 * _MASS_/docs
 * _MASS_/packages
 
## 4) Adding custom stuff
Where ever possible, it would be great if you could share the marvelous things you come up with. The reality of this type of program is that it will most often be used in businesses and there will be some company specific things that are confidentuial and shouldn't be shared. In that case, the "Private" section below will be your guide.

### What to create?
Almost always, creatig a macro and/or template is the way to go. There is a lot of functionality available now, and that should be enough to do most things. Where it's not, create a module. Specifically:

 * A macro brings together internal functionality and other macros.
 * A template displays the output of a macro. That output can from from normal macros or macros embedded in the template. This is useful for creating configuration files, particularly where there are sections of non-gererated content, or if the content has more than one form.
 * Modules create internal functionality. Sometimes this is the tidiest way to do it.

### Detailed howtos
For information about how to create macros, templates & modules, see one of these guides below:

 * [creatingAMacro.md](creatingAMacro.md) - Your first stop for adding new functionality.
 * [creatingATemplate.md](creatingATemplate.md) - Format the output.
 * [creatingAModule.md](creatingAModule.md) - Write foundation functionality using PHP.
 * [creatingAPacakge.md](creatingAPacakge.md) - Put it all together.

### Shareable
I suggest doing a linked install for development as it allows you to quickly test things while easily keeping git up-to-date. 

Save your work in the relevant one of the -available folders and create a symlink to it from the relevant -enabled folder.

See [paths.md](paths.md) for more information.

### Private
Once again I suggest doing a linked install as you will almost definitely have some stuff that you can contribute back. For what you can't, create your work in a separate location **outside the mass git repository** (that you will probably want in your own source control) and then symlink each directory to profiles/commandLine/packages/companyName-thing. Here's an example (written in full):

    cd ~/.mass/profiles/commandLine/packages
    ln -sf ~/work/repos/companyRepo/stuffForMass companyName-convenienceMacros

A good example of the sort of things you might do this with is at the place I'm working, we have a large number of convenience macros like --live, --staging, --dev etc. These contain regex rules that are specific to our naming convention.
