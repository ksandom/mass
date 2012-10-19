# Introduction

This is in very early stages, but it is quite useable. I have been using it for a few weeks to import hosts from AWS. The dream is to be able to take a list of hosts and apply AWS actions to them like tagging, rebooting or assigning storage to them.

For a list of all current AWS functionality, run `mass --help=AWS`

# Importing hosts and other stuff from AWS

This works by using the "name" tag that you can set for individual hosts. For now this is a requirement to be able to import hosts from AWS.

There are currently a few ways of importing hosts from AWS.

If you want it to just work, then you'll want to set up your credentials (you only need to do this once). You can do this like so the below credentials are invalid, but are made to look a bit like the credentials you will recieve:

    mass --AWSSaveCred=dev,ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg

From there, you can run:

    mass --importFromAWS

Although I recommend using -vv to help you understand what's going on better in the beginning:

    mass -vv --importFromAWS

Probably that's all you'll need. If not, or if you want to know more, read on.

## Using Saved credentials

### Overview

This is currently the most convenient way of importing hosts (and other stuff) from AWS.

It works by using the PHP AWS API SDK (that's a mouth-full!) It 100% relies on this being present. You can find out where mass is expecting to find it using `mass --AWSLibraryDetails`

The version of the SDK is also important. Earlier versions ignore the credentials defined at runtime and instead only use the credentials specified in ~/.aws/???/config.php if you only need to import from one account this may be ok for you. The symptom is that every import will return the hosts defined in that file.

TODO fill in the ??? (I think it's php)

### Save your credentials

**You only need to do this once.**

In the most basic form:

    mass --AWSSaveCred=dev,ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg

This creates some credentials called "dev" where the key is "ASDFDGFHFSE5FG4" and the secret is "vdgnm8i7u6htgf4edwety543d2fg" (made up credentials). Since you can name each set of credentials, you can put in credentials for multiple accounts and import the hosts from all of them in one go.

### Import the hosts (and other stuff)

    mass --importFromAWS

Assuming you've set the credentials, that's all you need to do. Done!

## Runtime defined credentials

### Overview

If you don't want to save your credentials in mass, you can now run them using `--importHostsFromAWSDirect`.

## Import the hosts and specify your credentials

    mass --importHostsFromAWSDirect=ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg

## Manually

### Overview

In this method, we are not storing the credentials. Therefore we must specify them every time we want to make the call.

### Set your credentials

    mass --AWSSetCred=ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg

Note that this is non-persistent, so we haven't really achived anything useful here.

### Import the hosts

    mass --AWSSetCred=ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg --AWSGetHosts

Great, now we have hosts, but once again this is non-persistent. So we need to save them.

### Save them somewhere

    mass --AWSSetCred=ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg --AWSGetHosts --saveToJson=~!General,configDir!~/data/1LayerHosts/manuallyImported.json

Now we've saved them so that we can query them with `mass --list=blah`.

# Installing the PHP AWS API SDK

## Installing the official PHP AWS API SDK

    sudo apt-get install php-pear
    sudo pear channel-discover pear.amazonwebservices.com
    sudo pear install aws/sdk
    sudo apt-get install php5-curl

## Installing the forked PHP AWS API SDK

The advantage of doing this is that adds support for route53. If you don't need this for now, use the official one since that will always be the most up-to-date in every other way.

TODO Fill this out when it's ready.

_Comming soon_