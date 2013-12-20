# Calling mass mass from within scripts
## Introduction

*If you find yourself needing `--noColour` you are certainly doing it wrong and need to continue reading this document.*

Mass is built to interact with users and scripts. The user output regularly changes as new features are added and removed. It will also change depending on the context of the data. It is therefore not suitable for parsing with external tools.

To get output suitable for external tools, you need to specify what data you want like this

    mass --list --toString="~%hostName%~ ~%IP%~" --singleString | head

## Detail

    mass --list --toString="~%hostName%~ ~%IP%~" --singleString | head

The important part of this line is `--toString="~%hostName%~ ~%IP%~" --singleString`.

* `--toString="~%hostName%~ ~%IP%~"` says that we want the hostName field and the IP field and we want to separate them with a space.
* `--singleString` says that we want to output the result as one continuous string rather than using a user template.

Note that `| head` is just an example of piping that output to another program. It would actually be much more efficient to do `--first=10` since then on the needed data is processed all the way to the end.

### What fields are available?

You can use `--nested` like this

    mass --list --nested

Although you probably just want one since there will be lots of fields available for every result

    mass --list --nested --first

This will produce some very long output like this

    ksandom@zelappy:~$ mass --list=dev01 --nested
    
      0: 
        groupSet: 
          item: 
            0: 
              groupId: <CENSORED>
              groupName: <CENSORED>
            1: 
              groupId: <CENSORED>
              groupName: <CENSORED>
        instanceId: <CENSORED>
        imageId: ami-61b28015
        instanceState: 
          code: 16
          name: running
        keyName: <CENSORED>
        instanceType: m1.xlarge
        launchTime: 2012-03-13T11:07:04.000Z
        placement: 
          availabilityZone: eu-west-1b
          groupName: 
          tenancy: default
        kernelId: aki-62695816
        monitoring: 
          state: disabled
        architecture: x86_64
        rootDeviceType: ebs
        rootDeviceName: /dev/sda1
        blockDeviceMapping: 
          item: 
            0: 
              deviceName: /dev/sda1
              ebs: 
                volumeId: vol-0fd9d566
                status: attached
                attachTime: 2011-11-29T08:37:56.000Z
                deleteOnTermination: true
            1: 
              deviceName: sdf
              ebs: 
                volumeId: vol-fddad694
                status: attached
                attachTime: 2011-11-29T08:49:01.000Z
                deleteOnTermination: false
        virtualizationType: paravirtual
        tagSet: 
          item: 
            0: 
              key: <CENSORED>
              value: <CENSORED>
            1: 
              key: <CENSORED>
              value: <CENSORED>
            2: 
              key: Name
              value: <CENSORED>
        hypervisor: xen
        hostName: <CENSORED>
        internalFQDN: <CENSORED>
        externalFQDN: <CENSORED>
        externalIP: <CENSORED>
        internalIP: <CENSORED>
        location: <CENSORED>
        environment: development
        bastion: 
        pseudoID: 1321298940
        color: brightRedHLBlue
        filename: dev.json
        categoryName: default
        key: 0
        IP: <CENSORED>
        FQDN: <CENSORED>


## A worked example

A few times I've been asked how to call mass from inside another script/program. In this particular case I was asked how to get the number of servers that we currently have. Here is my answer:

    ksandom@zelappy:~$ mass --list --count --nested
    
      0: 436

Or more correctly, we should exclude localhost which isn't a server:

    ksandom@zelappy:~$ mass --list --excludeEach=local --count --nested 
    
      0: 435

If you'd like to use it within a script:

    ksandom@zelappy:~$ mass --list --excludeEach=local --count --singleString
    435


It's not a good idea to pipe the templated output since the templates change as functionality is added. Instead it's better to ask for the information you want:

    ksandom@zelappy:~$ mass --list --toString="~%hostName%~ ~%IP%~" --singleString | head
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>
    <CENSORED> <CENSORED>

Or for the original question:

    ksandom@zelappy:~$ mass --list --excludeEach=local --toString="~%hostName%~ ~%IP%~" --singleString | wc -l
    435


You can find out what information you can ask for in --toString like so:

    mass --list --first --nested
    
      0: 
        groupSet: 
          item: 
            0: 
              groupId: <CENSORED>
              groupName: <CENSORED>
            1: 
              groupId: <CENSORED>
              groupName: <CENSORED>
            2: 
              groupId: <CENSORED>
              groupName: <CENSORED>
        instanceId: <CENSORED>
        imageId: <CENSORED>
        instanceState: 
          code: 16
          name: running
        keyName: <CENSORED>
        instanceType: m1.small
        launchTime: 2012-10-18T12:24:03.000Z
        placement: 
          availabilityZone: eu-west-1a
          groupName: 
          tenancy: default
        kernelId: <CENSORED>
        monitoring: 
          state: disabled
        subnetId: <CENSORED>
        vpcId: <CENSORED>
        sourceDestCheck: false
        architecture: i386
        rootDeviceType: ebs
        rootDeviceName: /dev/sda1
        blockDeviceMapping: 
          item: 
            0: 
              deviceName: /dev/sda1
              ebs: 
                volumeId: <CENSORED>
                status: attached
                attachTime: 2012-10-18T12:24:08.000Z
                deleteOnTermination: true
            1: 
              deviceName: <CENSORED>
              ebs: 
                volumeId: <CENSORED>
                status: attached
                attachTime: 2012-10-18T12:34:33.000Z
                deleteOnTermination: false
        virtualizationType: paravirtual
        tagSet: 
          item: 
            0: 
              key: Name
              value: <CENSORED>
            1: 
              key: environment
              value: <CENSORED>
            2: 
              key: region
              value: <CENSORED>
        hypervisor: xen
        hostName: <CENSORED>
        internalFQDN: <CENSORED>
        externalFQDN: <CENSORED>
        externalIP: <CENSORED>
        internalIP: <CENSORED>
        location: <CENSORED>
        environment: <CENSORED>
        bastion: <CENSORED>
        preferredInterface: internal
        IP: <CENSORED>
        FQDN: <CENSORED>
        pseudoID: 963666206
        color: brightBlue
        filename: dev.json
        categoryName: default
        key: 0

# More information

You can find out feature used in this document like so:

    ksandom@zelappy:/usr/files/develop/mass$ mass --help=--nested
    Available features:
    --nested ~ debug,dev,output,all,CommandLine (CommandLine)
      Print output using a simple nested format. Particularly useful for debugging.
