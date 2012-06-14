# The basics from a user perspective

## Searching hosts

`mass --list=db` lists all hosts with db in their name.

`mass --list=^db.*live` lists all hosts whose name begins with db and has live somewhere in the name. It's a regex.

`mass --list` lists eeeeeeeverything. You probably won't want this option very often, but it's useful when working with imports and exports.

## Taking action

`mass --list=^db.*live --term` opens a terminal to each host.

`mass --list=^db.*live --screen` creates a screen session on your local machine with a ssh connection to each host in it's own screen window.

`mass --list=^db.*live --download=/etc/my.cnf` downloads /etc/my.cnf into a folder named by hostname for every matching host. Now checkout --uploadM2M!

`mass --list=^db.*live --uploadM2M=/tmp/my.cnf` uploads the my.cnf for each host to /tmp/my.cnf on each server.

`mass --list=^db.*live --upload=/tmp my.cnf` uploads my.cnf to /tmp on every matching host.

There's so much more to mass than this. And so much more that it will be. Take a look at `mass --help=all` to get an idea of what's coming.

## Getting help

Typing `mass` by itself will complain that you didn't ask it to do anything and then display the help that you would get if you had typed `mass --help`.

NOTE In the future `--help` may not be the default action, so if you want help it is a good idea to specify it with `--help`.

You can get more specific help by specifying a tag. These are listed at the bottom of the help output if you haven't specified any. Tags begining with a capital letter are Modules. Typing one of those will give you all the commands for that module.

## Getting data to use

See install.md. If you are using a company specific install, this is probably done for you.

# The basics under the hood

Mass works using a bus/resultset for features to communicate with each other. Generally you:

1. Put stuff in the bus 
2. Do stuff to that stuff
3. Take action on that stuff

Eg `mass --search=local --chooseFirst=IP,externalIP,internalIP --resultSet=cmd,top --toString='~!Terminal,GUICMD!~' --exec`

Which:

1. searches for all hosts containing local somewhere.
2. takes the first available IP out of externalIP & internalIP and sticks it in a variable called IP.
3. Sets the `cmd` variable to `top` so that we will run the top command on each host.
4. takes all the details to make a single string we can execute per result. `~!Terminal,GUICMD!~` expands to something like `xterm -e bash -c "ssh -t ~%IP%~ ~%cmd%~" &` (originally set in defaultVales.macro), which in turn will resolve to something like `xterm -e bash -c "ssh -t 127.0.0.1 top" &`
5. execute every string we recieve. As you might imagine, some care should be taken when using this.

The end result is we open a terminal to every matching host.

As a user you'd do something more like this:

`mass --list=local --term top`

Which does all the same stuff using list.macro and term.macro. `top` doesn't have a `--` in front of it, so it gets put in the ~!Global,stray!~ variable. Note that it doesn't matter where in the command these come from. Ie `mass --list=local echo --term top` would give very different results.
