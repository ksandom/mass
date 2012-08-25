By default mass just let's SSH work out the best credentials to use. For most situations, this works really well. But there are times when you want to do something custom. For example, when you use one set of credentials to connect to one set of servers and another set of credentials for another set of servers.

There are now two ways of getting around this:

# Regex based manipulations

## A Using the Import,readyForManipulations event

This event has the advantage that the maipulations will be done only on the import, therefore keeping everything running faster for everything else. It has the disadvantage that anything run afterwards will override what this does when ever there are collisions.

You can do this by creating a macro called personal.macro in /macros-available (and then symlink to it from macros-enabled within the appropriate profiles). The script should look something like this:

    # Set custom credentials for different hosts ~ personal
    #onDefine registerForEvent Import,readyForManipulations,personal
    
    resultSet userAt,ksandom@
    
    manipulateItem hostName,dev,resultSet auth,-i ~/.ssh/id_rsa-dev
    manipulateItem hostName,stag,resultSet auth,-i ~/.ssh/id_rsa-stag
    manipulateItem hostName,live,resultSet auth,-i ~/.ssh/id_rsa-live


## B Using the List,finished event

You can also use the List,finished event using the method described above. It's useful for testing, but adds area weight to your day to day usage of mass and should generally be avoided.

# A blanket resultSet

Given the original scenario, this is probably not what you want, but it may be helpful to you.

Create a macro called personal.macro in the mass home folder in /macros-available (and then symlink to it from macros-enabled within the apporpriate profiles). That script should look something like this:

    # Set personal credentials ~ personal
    #onDefine registerForEvent List,finished,personal
    
    resultSet userAt,ksandom@
    resultSet auth,-i ~/.ssh/id_rsa

In this case `ksandom@` is prepended to each server name, and `-i ~/.ssh/id_rsa` is put a bit earlier in the command. Note that I have not worked out a way to do this second part in clusterSSH without using ~/.ssh/config. If you know a way, please let me know or have a go yourself in /packages/SSH/clusterssh.macro.
