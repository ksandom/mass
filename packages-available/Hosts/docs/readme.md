# Hosts

Provides a way to store and search/retrieve hosts.

## Using it

* Make sure `Hosts` is included via [repoParms](https://github.com/ksandom/achel/blob/master/docs/programming/creatingARepositoryWithProfiles.md#use-repoparmdefinepackages-to-create-a-profile).
* [Get some hosts](https://github.com/ksandom/mass/blob/master/docs/install.md#2-getting-data-to-use).
* [Do stuff](https://github.com/ksandom/mass/blob/master/docs/gettingStarted.md) with those hosts.

## A worked example

Let's register my laptop

    $ mass --createHost=zelappy,192.168.1.120,zelappy.example.com

Here we've said

* It's short hostname is `zelappy`
* It's IP is `192.168.1.120`
* It's FQDN (full qualified domain name) is `zelappy.example.com`

Now let's search for it

    $ mass --list=lap
    zelappy / 192.168.1.120 / 
      zelappy.example.com 
      ~%instanceType%~ / ~%architecture%~ / ~%rootDeviceType%~
    1 hosts

Let's open up a terminal to it

    $ mass --list=lap --term

There's sooooo much more that you can do with this. I suggest [starting here](https://github.com/ksandom/mass/blob/master/docs/gettingStarted.md).
