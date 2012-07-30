By default mass just let's SSH work out the best credentials to use. For most situations, this works really well. But there are times when you want to do something custom. For example, when you use one set of credentials to connect to one set of servers and another set of credentials for another set of servers.

I've now implemented the beginnings of a solution to get around this. It's going to get a lot better, but it should already be useful to you.

Create a script in the mass home folder in /macros-available (and then symlink to it from macros-enabled). That script should look something like this:

    # Set personal credentials ~ personal
    #onDefine registerForEvent List,finished,personal
    
    resultSet userAt,ksandom@
    resultSet auth,-i ~/.ssh/id_rsa

In this case `ksandom@` is prepended to each server name, and `-i ~/.ssh/id_rsa` is put a bit earlier in the command. Note that I have not worked out a way to do this second part in clusterSSH without using ~/.ssh/config. If you know a way, please let me know or have a go yourself in /packages/SSH/clusterssh.macro.
