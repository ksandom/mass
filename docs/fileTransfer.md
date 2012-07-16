# --download

# --upload

# --upload12M

# --uploadM2M

# --serverToServers - Copying files from one or more servers to many servers

This copies files down to a temporary directory on your local machine, then uploads them to each machine that matches. To make this work, you need to 

* specify what server(s) the files are coming from with `--from`
* specify what server(s) the files are going to `--to`
* specify what files need to be copied

`--from` and `--to` work in the same way as `--list` except they send their result to a specific place for use with another script. It's simply a regular expression on the hostname. Typically you'd do something like

    mass --from=db.*01 --to=api.*

Which would give you output something like

    Found hosts - from
    
    default/ dbserver01 (46.1.1.1)
      external: dbserver01.external.example.com (46.1.1.1)
      internal: dbserver01.internal.example.com (10.1.1.1)
    
    Found hosts - to
    
    default/ apiserver01 (46.1.1.2)
      external: apiserver01.external.example.com (46.1.1.2)
      internal: apiserver01.internal.example.com (10.1.1.2)
    default/ apiserver02 (46.1.1.3)
      external: apiserver02.external.example.com (46.1.1.3)
      internal: apiserver02.internal.example.com (10.1.1.3)

Once you're happy that you are that you are looking at the right servers, you can give it an action. In this case --serverToServers

    mass --from=db.*01 --to=api.* --serverToServers=~/*

In this example, we are copying the our home directory from dbserver01 to a couple of API servers.
