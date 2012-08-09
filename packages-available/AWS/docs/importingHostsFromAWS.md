# Introduction

This is in very early stages, but it is quite useable. I have been using it for a few weeks to import hosts from AWS. The dream is to be able to take a list of hosts and apply AWS actions to them like tagging, rebooting or assigning storage to them.

There are currently a couple of ways of importing hosts from AWS.

For a list of all current AWS functionality, run `mass --help=AWS`

# Importing hosts from AWS

This works by using the "name" tag that you can set for individual hosts. For now this is a requirement to be able to import hosts from AWS.

## Using Saved credentials

### Overview

This is currently the most convenient way of importing hosts from AWS.

### Save your credentials

**You only need to do this once.**

In the most basic form:

    mass --AWSSaveCred=dev,ASDFDGFHFSE5FG4,vdgnm8i7u6htgf4edwety543d2fg

This creates some credentials called "dev" where the key is "ASDFDGFHFSE5FG4" and the secret is "vdgnm8i7u6htgf4edwety543d2fg" (made up credentials). Since you can name each set of credentials, you can put in credentials for multiple accounts and import the hosts from all of them in one go.

### Import the hosts

    mass --importHostsFromAWS

Assuming you've set the credentials, that's all you need to do. Done!
