# Introduction

This is in very early stages, but it is quite useable. I have been using it for a few weeks to import hosts from AWS. The dream is to be able to take a list of hosts and apply AWS actions to them like tagging, rebooting or assigning storage to them.

For a list of all current AWS functionality, run `mass --help=AWS`

# Importing hosts from AWS

This works by using the "name" tag that you can set for individual hosts. For now this is a requirement to be able to import hosts from AWS.

There are currently a few ways of importing hosts from AWS.

## Using Saved credentials

### Overview

This is currently the most convenient way of importing hosts from AWS.

It works by using the PHP AWS API SDK (that's a mouth-full!) It 100% relies on this being present. You can find out where mass is expecting to find it using `mass --AWSLibraryDetails`

The version of the SDK is also important. Earlier versions ignore the credentials defined at runtime and instead only use the credentials specified in ~/.aws/???/config.php if you only need to import from one account this may be ok for you. The symptom is that every import will return the hosts defined in that file.

TODO fill in the ??? (I think it's php)

### Save your credentials

**You only need to do this once.**

In the most basic form:

    mass --AWSSaveCred=dev,ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg

This creates some credentials called "dev" where the key is "ASDFDGFHFSE5FG4" and the secret is "vdgnm8i7u6htgf4edwety543d2fg" (made up credentials). Since you can name each set of credentials, you can put in credentials for multiple accounts and import the hosts from all of them in one go.

### Import the hosts

    mass --importHostsFromAWS

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

## Installing the PHP AWS API SDK

TODO write this.