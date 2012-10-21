# Conditions

# Looping

## loopMacro

Loop macro is a way of easily interacting with results. Each item in the result set is iterated through and it's variables are made available to you in the form ~!Result,variableName!~. You can make any changes you want to that variable, and when your macro exits for that iteration, the changes will be saved back into the result. Let's extend the exmaple from [creatingAMacro.md](creatingAMacro.md)

    # mass --list=ex --nested
    
    0: 
      filename: example.hosts.json
      categoryName: example
      hostName: example00-dev
      internalIP: 192.168.1.10
      externalIP: 
      internalFQDN: example00-dev.internal.example.com
      externalFQDN: example00-dev.external.example.com
      IP: 192.168.1.10
      FQDN: example00-dev.internal.example.com
    1: 
      filename: example.hosts.json
      categoryName: example
      hostName: example01-dev
      internalIP: 192.168.1.11
      externalIP: 
      internalFQDN: example01-dev.internal.example.com
      externalFQDN: example01-dev.external.example.com
      IP: 192.168.1.11
      FQDN: example01-dev.internal.example.com
    2: 
      filename: example.hosts.json
      categoryName: example
      hostName: example02-dev
      internalIP: 192.168.1.12
      externalIP: 
      internalFQDN: example02-dev.internal.example.com
      externalFQDN: example02-dev.external.example.com
      IP: 192.168.1.12
      FQDN: example02-dev.internal.example.com

Here we have 3 hosts. Let's create a macro called loopMacroDemo.macro:

    # Demo loopMacro macro ~ demo
    
    set Result,sillyText,Here is the IP: ~!Result,IP!~ and the domain name: ~!Result,FQDN!~
    set Result,someMoresillyText,I am a lovely donkey
    set Result,combined,~!Result,someMoresillyText!~. Yeeeees... ~!Result,sillyText!~

So now if we add `--loopMacro=loopMacroDemo` to the command, we'll get something like this:

    # mass --list=ex --nested --loopMacro=loopMacroDemo
    
    0: 
      filename: example.hosts.json
      categoryName: example
      hostName: example00-dev
      internalIP: 192.168.1.10
      externalIP: 
      internalFQDN: example00-dev.internal.example.com
      externalFQDN: example00-dev.external.example.com
      IP: 192.168.1.10
      FQDN: example00-dev.internal.example.com
      sillyText: Here is the IP: 192.168.1.10 and the domain name: example00-dev.internal.example.com
      someMoresillyText: I am a lovely donkey
      combined: I am a lovely donkey. Yeeeees... Here is the IP: 192.168.1.10 and the domain name: example00-dev.internal.example.com
    1: 
      filename: example.hosts.json
      categoryName: example
      hostName: example01-dev
      internalIP: 192.168.1.11
      externalIP: 
      internalFQDN: example01-dev.internal.example.com
      externalFQDN: example01-dev.external.example.com
      IP: 192.168.1.11
      FQDN: example01-dev.internal.example.com
      sillyText: Here is the IP: 192.168.1.11 and the domain name: example01-dev.internal.example.com
      someMoresillyText: I am a lovely donkey
      combined: I am a lovely donkey. Yeeeees... Here is the IP: 192.168.1.11 and the domain name: example01-dev.internal.example.com
    2: 
      filename: example.hosts.json
      categoryName: example
      hostName: example02-dev
      internalIP: 192.168.1.12
      externalIP: 
      internalFQDN: example02-dev.internal.example.com
      externalFQDN: example02-dev.external.example.com
      IP: 192.168.1.12
      FQDN: example02-dev.internal.example.com
      sillyText: Here is the IP: 192.168.1.12 and the domain name: example02-dev.internal.example.com
      someMoresillyText: I am a lovely donkey
      combined: I am a lovely donkey. Yeeeees... Here is the IP: 192.168.1.12 and the domain name: example02-dev.internal.example.com

TODO This is **way** underselling what this can do. Write a more advanced example.

TODO Document what --importFromAWS does as it is an existing example of loopMacro in action.

## forEach

forEach does a very similar job to loopMacro, but with an important difference:

* loopMacro puts the contents of each iteration into the `Result` store with each value key being references like so `~!Result,internalIP!~`.
* forEach puts the contents of each iteration into the resultset (temporarily replacing the existing resultset). This makes multiple layer nesting very natural.

At the end of the execution, both replace the items in the original resultset with what ever manipulations have been applied.

TODO write more on this.

TODO add an example.