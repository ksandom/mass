#!/bin/bash
# Install the program
# Copyright (c) 2013, Kevin Sandom under the BSD License. See LICENSE for full details.

echo "NOTE that using this install script is deprecated. From readme.md
"

readmePath="`dirname $0`"/readme.md
grep -A 2 '^# Install' "$readmePath"

echo "
Will now call the new way"

sleep 3

export extraSrc="git@github.com:ksandom/mass.git"
curl https://raw.github.com/ksandom/achel/master/supplimentary/misc/webInstall | bash

