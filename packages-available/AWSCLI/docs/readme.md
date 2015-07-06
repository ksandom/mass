# AWS CLI

This is the replacement for the AWS package.

It uses the Amazon's [AWS CLI](http://aws.amazon.com/cli/) to import data into mass.

## Using it

* Get the [AWS CLI](http://aws.amazon.com/cli/) installed and setup.
* Run `mass --awsGetAll` to import everything that mass currently knows how to get. (Note that IAM permissions will affect what it is able to get.)

If you'd like to just import some stuff, find what you can important separately using `mass --help=awsImport`.
