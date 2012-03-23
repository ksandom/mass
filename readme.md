# Mass
is a modularised script for managing several nodes of a cluster. This undersells it... I'll think of a better description soon.

# Install
See docs/install.md
Use install.sh.

* If you run it as root it will install system wide (currently untested!).
* If you run it as yourself, it will install locally in your account.
* Alternatively, if you run it as yourself with linked at the end (`./install.sh linked`), it will be linked to the repo. *This option makes the most sense if you want to do development*.

For now, read install.sh to see what it does.

# History
I've written a version of this script at every company I've worked at since 2007 and it's always been a big hit. In each case it was very specific to the architecture of the given place, so it wasn't very portable.

Yet again the scenario came up where it would be useful, so this time I've developed it entirely in my own time, which means I can take the time to do it right. Now it's at a point that it will be useful at work and I'm ready to release it. I'm not including any company specific information/data in the project (that would be silly!), so it probably won't be useful to other people for a few more weeks. By then I intend to have tools which generate that data.
 