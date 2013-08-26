# Introduction

After importing some hosts, you can do stuff. Let's talk about that stuff.

# Searching

Typically you search for hosts like this:

    mass --list=dev

This would include all servers that match "dev" in any part of the data set for that server. "dev" is actually regex, so you could do something like `mass --list=^dev` to make it more precise by being more specific. That example specifies that any string that matches must begin with "dev".

Typically I use `--requireItem` in a macro to specify exactly which field I require.

# Refining/Working with results

## First, last and offset

* `--first=integer` selects only the first "integer" number of results in the result set. If "integer" is not specified, 1 is assumed.
* `--last=integer` selects only the last "integer" number of results in the result set. If "integer" is not specified, 1 is assumed.
* `--offsetResult` may be of interest to you. See `mass --help=--offsetResult` to see how it works.

## Refine, require and exclude

These are very helpful tools in getting the results you want.

* `--refine=regex` is an alias for `--requireEach=regex`.
* `--requireEach=regex` selects results where any of their keys match the regex.
* `--requireItem=valueKey,regex` selects results where the value of a specific key matches the regex.
* `--excludeEach=regex` selects results where all keys do not match the regex.
* `--excludeItem=valueKey,regex` selects results where the value of a specific key does not match the regex.

# Displaying output
## Less is more

For functionality supporting semantics (which includes pretty much everything to do with hosts), you can use `--less` and `--more`.

* --left chooses a more compact version of the output.
* --more chooses a more verbose version of the output.

## Null and verbosity

### Overview

It's important to recognise the different between data/resultSet output and debug output. These are controlled individually.

* data/resultSet output is data that you have queried, refined and chosen to output in a particular format. This is what you will do most of the time, and is what I'm referring to when I say choosing your output.
* debug output is intented for troubleshooting and shouldn't be visible on a normal basis (particularly for mature features). Setting the verbosity directly changes the debugging level. While pretty much any integer is possible, only -1 to 5 are used at the moment:
 * -1 Used internally. You should never set this.
 * 0 No output.
 * 1-3 Typically for debugging user level stuff.
 * 3-5 Typically for debugging libraries. This gets very very talkative!

### Basic output

* resultSet: --null chooses a blank resultSet output.
* resultSet: --more and --less influences the resultSet output. See "Less is more" above.
* debug: -q sets the verbosity to -1. If this is the first parameter it will suppress debug0 warnings on borked/mismatched code.
* debug: -v increments the verbosity
* debug: -V decrements the verbosity
* debug: --verbosity=integer sets the verbosity.

If verbosity is greater than 0, some macros will give you some extra output

## EEEEEEEEEEEEVERYTHIIIIING

Sometimes you want to see all the data that comes back, and not just what the active template shows you. For this you can use:

* `--nested` uses a compact format to display data and show structure.
* `--print_r` uses PHP's print_r() function to display the data in a format that may be more familiar for some people.

You may also be interested in `--json` which outputs it in json format, although you'll need to use a tool like jsonlint to make human friendly.

# Taking action

* `--cssh` uses clusterSSH to open and control a terminal to every server in the resultSet. This seems to be the most popular feature amoungst people I've talked to who use mass. For convenience, you can use `manageMass installClusterSSH` to install it on Linux or OSX (NOTE that on OSX, this script requies brew.)
* `--term` opens up an isolated terminal to every server in the resultSet. If this doesn't work for you, feel free to debug it with `-v` and let me know what the command should look like on your platform.
* `--upload` uploads a file/directory to to each server in the resultSet. See `--help=--upload` for more details.
* `--download` uploads a file/directory to to each server in the resultSet. See `--help=--download` for more details.

