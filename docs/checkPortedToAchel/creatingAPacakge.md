Creating a package is really easy. It's a directory that goes in _MASS_/repos/mass/packages-available.

Quite simply all you need to do is put any macros, templates, modules and documentation in the folder. 

By convention I put documentation in a folder called docs within the package. Documentation is in the .md format, though **if you wanted to include pictures, then you must put them in the docs folder.**

    ksandom@lappyg:~/.mass/repos/mass/packages-available/AWS$ find
    .
    ./docs
    ./docs/importingHostsFromAWS.md
    ./route53.macro
    ./importHostsFromAWS.macro
    ./elb.template
    ./route53ToELBs.macro
    ./aws.php
    ./route53.template
    ./AWSSaveCred.macro
    ./importHostsFromAWSDirect.macro
    ./ELBsToInstances.macro
    ./importFromAWSAccount.macro
    ./importHostsFromAWSAccount.macro
    ./importFromAWS.macro
    ./ELB.macro

For more information on the directory structure, see [paths.md](paths.md).
