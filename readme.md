# Mass

has been replaced by [DevOpsDream](https://github.com/ksandom/devopsdream). This repo remains solely for legacy work-flows.

Mass is a modularised tool for managing several nodes of a cluster. Open individual SSH terminals to specific groups of nodes of your cluster, a single node, or the whole lot. Do the same with ClusterSSH or virtual terminals in screen. Upload a file to each node or dowload a file from each, prefixed with the hostname. Run a command to conquer the world... or conduct that essential maintenance. Essentially your nodes are one RegEx away.

Now, that's what was significant to me. It turns out though that a lot of people find it really useful as a tool for looking up servers.

Driving it all is a [full programming language](https://github.com/ksandom/achel) that is very different to anything I've seen before. I'm building it to scratch a very specific itch and I doubt the full picture it will be useful to many people. In fact, most of it isn't in the public repo yet. However the foundations, which are already released, are really useful for Sysadmin tasks as described above.

This is a tiny percentage of the final vision, so there's a lot more to come!

## Requirements

* PHP
* Bash

## Install

    export extraSrc="https://github.com/ksandom/mass.git"; curl https://raw.githubusercontent.com/ksandom/achel/master/supplimentary/misc/webInstall | bash

See [docs/install.md](mass/tree/master/docs/install.md) for more information.

## Important updates

* Mass has undergone significant refactoring. You can now install it using the one-liner below. [More info](mass/tree/master/docs/install.md).

As a general rule, when ever you update, you should re-run install.sh to apply any structural changes as the internals are regularly being refactored.

## Contributing

* If there's something you want mass to do that it doesn't currently, take a look at the "creatingA" series in the [documentation](tree/master/docs). It would be lovely if you can contribute is back.
* There are `TODO`'s floating around the documentation that need to be filled in. Filling these in would be very helpful.
* There are `# TODO`'s floating around in the code. There are going to be a few which I'll reserve for me. Typically I only do this if I've planned something else based on how that thing gets done.

The bottom line is, I wrote this tool because it's useful to me. If it's useful to you and you have something to contribute, it would be lovely for you to put it forward.

To submit it, create a pull request to the development branch. Make sure to include in the description
* What you are trying to achieve.
* Any relevant information that can help me test before and after that your code does what you are trying to achieve.

## History

I've written a version of this tool at every company I've worked at since 2007 and it's always been a big hit. In each case it was very specific to the architecture of the given place, so it wasn't very portable.

This time I've developed it entirely in my own time, which means I can take the time to do it right and I can share it and take it with me. Phase 1, which is available now is maturing and ready for public use.

A month or so in to development, it became apparent that this would be a fantastic set of foundations for a concept I came up with in late 2000/early 2001. This is phase 2. It doesn't replace anything, it simply adds a whole lot. Therefore you don't have to worry about any macros you create becoming obsolete because of it. More on this in due time. ;)
 
