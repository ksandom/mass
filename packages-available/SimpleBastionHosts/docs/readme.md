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
    manipulateItem hostName,staging,resultSet bastion,example
    manipulateItem hostName,live,resultSet bastion,anotherExample
    
    # If we are using a bastion host, we should use the internal address.
    manipulateItem bastion,..*,resultSet preferredInterface,internal

This will set the bastion server for hosts matching specific regexes, and then make sure the internal address is used to contact those hosts.

TODO Add output


## A worked example - after import

When you are doing the manipulations on the server that the hosts will be updated from, you can use this method. You could also use it in rare situations where you definitely won't be updating the hosts again. The caution is because the bastion changes will be lost when the defeinitions are replaced.

TODO write this

## More info

    $ mass --help=Bastion
