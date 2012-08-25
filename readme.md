# Mass
is a modularised tool for managing several nodes of a cluster. Open individual SSH terminals to specific groups of nodes of your cluster, a single node, or the whole lot. Do the same with CSSH or virtual terminals in screen. Upload a file to each node or dowload a file from each, prefixed with the hostname. Run a command to conquer the world... or conduct that essential maintenance. Essentially your nodes are one RegEx away.

Driving it all is a full programming language that is very different to anything I've seen before. I'm building it for a very specific itch and I doubt the full picture it will be useful to many people. In fact, most of it isn't in the public repo yet. However the foundations, which are already released, are really useful for Sysadmin tasks as described above.

This is a tiny percentage of the final vision, so there's a lot more to come!

# Requirements
* PHP
* Bash

# Important updates

* As of June 7th (on master) result variable syntax has changed from %varName% to ~%varName%~. See docs/changes/0000-x-resultVarSyntax.md

# Install
See docs/install.md
Use install.sh.

# History
I've written a version of this tool at every company I've worked at since 2007 and it's always been a big hit. In each case it was very specific to the architecture of the given place, so it wasn't very portable.

This time I've developed it entirely in my own time, which means I can take the time to do it right and I can share it and take it with me. I've been using it for quite some time now. There's information for how to import your hosts in docs/install.md.
 