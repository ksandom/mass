# The basics
It's really easy!

The first line is a `#` comment describing the macro with tags related to the macro at the end. The tags are separated from the comment using a ~. It looks like this:

    # Does something really awesome ~ awesome,lovely,taaaaaag

Note that there are no spaces between the tags, but there are around the `~`.After that everything is just using the commands you can see when you run `mass --help` with a couple of exceptions:

 * Leave off the `--` when choosing a name for the macro.
 * Use a space instead of an = to separate the command and the parameters.
 
For example if you were to do `mass --list=db --chooseFirst=IP,externalIP,internalIP` on the command line, as a macro it would look like:

    # List out all database servers setting IP to the value of externalIP, or internalIP if externalIP is not set. ~ dbs,database,user
    
    list db
    chooseFirst IP,externalIP,internalIP
 
Assume we save that as `repos/custom/macros-available/listdbs.macro` and then symlink that to `profiles/commandline/macros/listdbs.macro`, we could invoke it from the command line as --listdbs or if from inside other macros, it would be `listdbs`. For more information on the directory structure, see [paths.md](paths.md).

See examples/example.macro for a working example.

# Taking parameters
If the macro was passed a parameter, it will show up in ~!Global,listdbs!~.

Say we want to list out all servers that begin with db and continue with what ever the user specifies, it could look like this:

    # List out all database servers setting IP to the value of externalIP, or internalIP if externalIP is not set. ~ dbs,database,user
    
    list db~!Global,listdbs!~
    chooseFirst IP,externalIP,internalIP


# Variables
There are currerntly two types of variables with very different purposes.

 * Result variables - `~%resultKey%~` - reference items within the result of commands you invoke. This is primarily used to retrieve parts of a result.
 * Store variables - `~!Category,variableName!~` - reference items within the store. This is primarily used for working with configuration (both mass and your macros) and parameters.

## Result variables
`~%resultKey%~` - *reference items within the result of commands you invoke. This is primarily used to retrieve parts of a result.*

The default behavior of unmatched **result variables** is to evaluate to **leave them unparsed**. You can override this behavior by adding `--cleanUnresolvedResultVars` after the last place that might resolve them.

Say we typed `mass --list=ex --nested`. We might get a result like this:

    # mass --list=ex --nested
    
    0: 
      filename: example.hosts.json
      categoryName: example
      hostName: example01-dev
      internalIP: 192.168.1.10
      externalIP: 
      internalFQDN: example01-dev.internal.example.com
      externalFQDN: example01-dev.external.example.com
      
We have an internal IP address, and no external IP address for this result. Often we may want to choose the external one, but in this case we want the internal one. In any case, we want only one. So we can use the `--chooseFirst` feature to set IP to the value we want. Therefore we'd run `--chooseFirst=IP,externalIP,internalIP` which would produce:
      
    # mass --list=ex --nested --chooseFirst=IP,externalIP,internalIP
    
    0: 
      filename: example.hosts.json
      categoryName: example
      hostName: example01-dev
      internalIP: 192.168.1.10
      externalIP: 
      internalFQDN: example01-dev.internal.example.com
      externalFQDN: example01-dev.external.example.com
      IP: 192.168.1.10

So you see we have set IP to something, giving preference to externalIP, and in this case being set to the internalIP since we don't have a value for the externalIP. That's great, but that was all internal via a function; we haven't actually tried retrieving anything from that IP variable yet. Let's create a series of strings which we can later execute like this `--toString="ssh %IP%"`:

    # mass --list=ex --nested --chooseFirst=IP,externalIP,internalIP --toString="ssh ~%IP%~"
    
    0: ssh 192.168.1.10

For completeness, let's execute that using `--exec`:

    # mass --list=ex --nested --chooseFirst=IP,externalIP,internalIP --toString="ssh ~%IP%~" --exec
    ssh: connect to host 192.168.1.10 port 22: Network is unreachable

As you can see, in my case I'm not connected to a network while I'm writing this. But if I had been, I would have had an interactive ssh session to that server. Also note that this execution is blocking the execution of the next match. So if there are multiple hosts matching our criteria, then the next ssh session would not begin until the current one ends (which would be fast since this is a fast fail). See "Spawning stuff" down below for more information.

## Store variables
`~!Category,variableName!~` - *reference items within the store. This is primarily used for working with configuration (both mass and macros) and parameters.*

The default behavior of unmatched **store variables** is to evaluate to **nothing**.

Let's continue with the example above. We ended up with `mass --list=ex --nested --chooseFirst=IP,externalIP,internalIP --toString="ssh ~%IP%~" --exec` which connects to the hosts via ssh one at a time. First let's get it to print out information about load and how long the box has been up:

    mass --list=ex --nested --chooseFirst=IP,externalIP,internalIP --toString="ssh ~%IP%~ uptime" --exec

Then by simply adding `~!Settings,backgroundTasks!~` we make the task happen in the background in parallel and just return when they are ready. But if the user specifies `--noBG` then we'll get the old behavior where tasks happen sequentially. So that looks like this:

    mass --list=ex --nested --chooseFirst=IP,externalIP,internalIP --toString="ssh ~%IP%~ uptime ~!Settings,backgroundTasks!~" --exec

The nice thing with this is that it gives us huge potential to create settable settings or essentially compile an intellegent action from multiple sources.

## The stray variable
Any parameters passed at the commandline lacking `-` or `--` will show up in ~!Global,stray!~ delimited with spaces (ie you can have lots of them mixed throughout the command line).

It's important to think about whether this is really your best option, as using this in a macro will likely lead to that macro becoming less nestable within other macros. USE WITH CARE.

# Spawning stuff

upload.macro is an exmaple of this stuff in action.

In the "Store variables" section, I mention ~!Settings,backgroundTasks!~ which you can append to commands that you want to happen in the background and in parallel. This works simply by appending an & at the end of the line, which works in most cases, but sometimes you need to redirect some output as well. Take a look at clusterssh.template (within the SSH package). While it is actually a template, it solves the same problem in the same way that I'm talking about here. It used to look like this:

    cssh ~!Cssh,extra!~ <~~ ~%userAt%~~%FQDN%~  ~> &

Say you list 3 hosts, this would produce

    cssh host1 host2 host3 &

But it would not return until the process terminates. So I changed it to this:

    cssh ~!Cssh,extra!~ <~~ ~%userAt%~~%FQDN%~  ~> ~!Settings,nullOutput!~ ~!Settings,backgroundTasks!~

Which resolves to:

    cssh host1 host2 host3 2>/dev/null 1>/dev/null &

If you now include `--noBG` before the `--cssh`, it will output stuff to the terminal and not return until it completes. It would look like this:

    cssh host1 host2 host3

This is useful for debugging.