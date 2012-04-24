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
### Templates

# Updating
Simply do a git pull where ever you checked out the code then run `./install.sh` in the same way you did under the install section.

# Stuff to think about after installing
## What stuff to enable
There are two reasons to consider what you want to enable:

1. Everything that is enabled is using memory all the time and takes time to load. Right now things are sufficiently small that this isn't an issue, but it's not hard to imagine this growing to a size where it's worth taking this into account.
2. In the future there may be alternate versions of the same functionality. Often this functionality will be mutually exclusive, so one will have to be chosen over the other.

### Macros
### Templates
### Modules

## Enabling or disabling stuff
It all works like available/enabled system that ubuntu uses via symlinks. You can use `ln -s` to create symlinks in the same way that you'd use cp to copy a file. Please do not simply copy the files as that will make things very hard to diagnose when there are problems.

Here's what my macros folder looks like right now:

    [ksandom@k4 macros-enabled]$ ls -l
    total 0
    lrwxrwxrwx 1 ksandom ksandom 45 Apr 24 09:35 createDefaultValues.macro -> ../macros-available/createDefaultValues.macro
    lrwxrwxrwx 1 ksandom ksandom 34 Apr 24 09:35 download.macro -> ../macros-available/download.macro
    lrwxrwxrwx 1 ksandom ksandom 31 Apr 24 09:35 hosts.macro -> ../macros-available/hosts.macro
    lrwxrwxrwx 1 ksandom ksandom 29 Apr 24 09:35 run.macro -> ../macros-available/run.macro
    lrwxrwxrwx 1 ksandom ksandom 32 Apr 24 09:35 screen.macro -> ../macros-available/screen.macro
    lrwxrwxrwx 1 ksandom ksandom 30 Apr 24 09:35 term.macro -> ../macros-available/term.macro
    lrwxrwxrwx 1 ksandom ksandom 33 Apr 17 16:44 updateHosts.macro -> ../macros-companyName/updateHosts.macro

Notice companyName in the last entry. That is how I'm separating out company specific stuff that should not be shared. See "Adding custom stuff" below.

## Adding custom stuff
Where ever possible, it would be great if you could share the marvelous things you come up with. The reality of this type of program is that it will most often be used in businesses and there will be some company specific things that are confidentuial and shouldn't be shared. In that case, the "Private" section below will be your guide.

### Shareable
I suggest doing a linked install for development as it allows you to quickly test things whlie easily keeping git up-to-date. 
TODO talk about paths

### Private
TODO talk about paths

### Paths explained futher