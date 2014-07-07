# Choosing the right interface.

Normally mass should just figure out the best way to connect to a host in any given situation, and you won't need to think about it further. There are some situations where it doesn't; For example when you import hosts from a hosts file that originated from a server that contains internal addresses. Those internal addresses won't be useful when you are outside that network.

This document describes what you can do about it.

## Preferred methods

## Short term hacks

### --useInterface - Choose which interface is best.

This method is great for on the fly when you need to get onto a machine in a hurry. It will lead to pain if you use it as a fix for a broken import process.

Let's say you have a listing

    $ mass --list=k1
    k1 / 192.168.0.91 / unknown location
      192.168.0.91 
      unknown type / unknown architecture / unknown rootDeviceType
    1 hosts

You can see what information is available like this

    $ mass --list=k1 --nested
    
      0: 
        hostNameMap: 
        hostnameCount: 1
        internalIP: 192.168.0.91
        hostName: k1
        hostnameMap: 
          192.168.0.91: 192.168.0.91
        hostName1: 192.168.0.91
        source: hosts file
        key: 0
        instanceType: unknown type
        location: unknown location
        architecture: unknown architecture
        rootDeviceType: unknown rootDeviceType
        filename: importedFromETCHosts.json
        categoryName: default
        IP: 192.168.0.91
        FQDN: 192.168.0.91
        chosenInterface: 192.168.0.91

Say you want to use the hostName. You can do it like this

    $ mass --list=k1 --nested --useInterface=hostName
    
      0: 
        hostNameMap: 
        hostnameCount: 1
        internalIP: 192.168.0.91
        hostName: k1
        hostnameMap: 
          192.168.0.91: 192.168.0.91
        hostName1: 192.168.0.91
        source: hosts file
        key: 0
        instanceType: unknown type
        location: unknown location
        architecture: unknown architecture
        rootDeviceType: unknown rootDeviceType
        filename: importedFromETCHosts.json
        categoryName: default
        IP: k1
        FQDN: k1
        chosenInterface: 192.168.0.91

The important thing to notice here is that the `IP` and `FQDN` have changed from `192.168.0.91` to `k1`.
