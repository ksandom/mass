# SimpleBastionHosts

Provides a simple way to tunnel Mass SSH connections through a single server. That server can be different for different groups hosts. For example live vs staging.

This was originally intended to be an over-simplified short term solution, but with a little tweaking became good enough that I never needed to revisit it. I likely will at some point, although it's not in the immediate plans.

## Using it

* Make sure `SimpleBastionHosts` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile).
* Add at least one bastion host.
* Manipulate existing hosts as required to denote which bastion host should be used in each case.
* `--term`, `--screen`, `--upload` etc should now all use the bastion host when connecting to the host.

## A worked example - during import

For hosts that you are likely to re-import at some point (eg AWS), you should do it this way.

Let's add a couple of bastion hosts

    $ mass --addSimpleBastionHost=example,zelappy
    $ mass --addSimpleBastionHost=anotherExample,k1

And manipulate the hosts during import. We'll call this applBastionManipulations

    # Apply bastion host manipulations during import ~ bastion,example
    #onDefine registerForEvent Import,readyForManipulations,applBastionManipulations
    
    # Set which bastion host to use for which servers.
    manipulateItem hostName,staging,
    	resultSet bastion,example
    	resultSet location,office
    
    manipulateItem hostName,live,
    	resultSet bastion,anotherExample
    	resultSet location,datacenter1
    
    # If we are using a bastion host, we should use the internal address.
    manipulateItem bastion,..*,
    	resultSet preferredInterface,internal

This will set the bastion server for hosts matching specific regexes, and then make sure the internal address is used to contact those hosts.

    $ mass --list=^web.*live --first --term
    [debug0]: Spawning commands in background (&)
    konsole -e bash -c "ssh  -t -o StrictHostKeyChecking=no -At k1 ssh   web01-live.example.com " 2>/dev/null 1>/dev/null &
    web01-live / 10.1.2.3 / datacenter1
      web01-live.example.com 
      c1.medium / i386 / ebs
    1 hosts

* First we added a couple of bastion definitions. These must match existing hosts. Eg `k1` and `zelappy` are a couple of computers of mine.
* Then we added a macro for setting the bastion settings. Importantly
 * The macro registers for the `Import,readyForManipulations` event.
 * We add specific rules for `staging` and `live`. The `bastion` is what we need here. `location` is just an example of something else you could set at the same time, and how you would go about it.

## A worked example - after import

*This method should only be used on a server which does not recieve updates from anywhere.* As soon as an update is performed, any changes are likely to be lost. Therefore this information is provided as an FYI and *should be considered experimental*.

Let's create a host

    $ mass --createHost=bella,127.0.0.1,bella.example.com

Now let's set the bastion

    $ mass --editHostsDefinitions=manual --setNested=Hosts-manual,bella,bastion,example

* First we created a host to use called `bella`.
* Then we set the `bastion` variable to `example`

## More info

    $ mass --help=Bastion
