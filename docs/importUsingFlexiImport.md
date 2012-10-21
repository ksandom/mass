Development of FlexiImport is currently suspended since it doesn't seem to be needed right now.

# What

FlexiImport is for important various text-based data structures.

# Example

This is a macro to import hosts that are saved in ~/.ssh/config. It assumes that the file has already been loaded into the resultset.

    	fiCreate sshConfig
    	fiNewRecordOn sshConfig,keep,^Host .*
    	
    	fiRuleDefine sshConfig,host,^Host (.*)$
    	fiRuleMap sshConfig,host,1,hostname
    	
    	fiRuleDefine sshConfig,hostname,^Hostname (.*)$
    	fiRuleMap sshConfig,hostname,1,externalFQDN
    	
    	fiRuleDefine sshConfig,key,^IdentityFile (.*)$
    	fiRuleMap sshConfig,key,1,key
    	
    	fiGo sshConfig
