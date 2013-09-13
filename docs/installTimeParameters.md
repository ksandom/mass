# Install time parameters

* --help - Show this help and exit.
* --showConfig - Show the settings that would be used and exit.
* --configDir="/etc/mass" - Where to save configuration files and data. It is the base directory for all configuration.
* --storageDir="/etc/mass" - Where you want persistent storage to go. The directories data and config will be created inside this directory. Normally this is the same as the configDir.
* --binExec=/usr/bin - Where to put the file to be execurted.
* --installType='cp' - Can be cp for copy, or ln for link.
* --defaults - Use this to go back to the default settings. This will migrate any data/settings you already have. This must be the first parameter.
* --dontDetect - Use this if install.sh is detecting the old settings incorrectly or if you want to start with the defaults. This will not migrate any data/settings that you already have. This must be the first parameter.

# Migration

You'll almost never need to migrate from one type of install to another since any standard changes will normally be taken care of for you, but if you do there's a couple of tricks in here which will make your life much easier.

If you want to migrate to a non-standard install, simply specify the parameters as above. Eg:

    ./install.sh --storageDir=/opt/mass

If you have a non-standard install and want to migrate to a standard one, use --defaults like this:

    ./install.sh --defaults

If you want to do this, but specify something different, you can do so like this:

    ./install.sh --defaults --setting1=blah --setting2=wooo

If you want to install to a new location regardless of where a previous install is located, you can do it as follows:

    ./install.sh --dontDetect --setting1=blah --setting2=wooo

This effectively skips the auto-detection step and uses the defaults. Anything you specify after that will override the defaults.

Note that --dontDetect implies --defaults. Only one of them may be specified at a time as they must be the first parameter.


Everyday help is at the beginning of this output.
