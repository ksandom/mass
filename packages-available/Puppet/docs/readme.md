# Puppet

Import hosts from puppet.

## Using it

* Make sure `Puppet` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile).
* Import data from puppet.

## A worked example

Assumptions

* You have an active puppet installation on the same machine.
* The current user has read access to `/var/lib/puppet` .

We import the data

    $ mass --importFromPuppet

Now we can query it

    $ mass --list=test01
    test01.example.com / 10.0.0.1 / unknown location
      test01.example.com
      vmware / x86_64 / Virtual disk
    1 hosts
