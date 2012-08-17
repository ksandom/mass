# Description

Results get lost after the second `ifResult` in testIf.macro. 

# Status

Fixed. There are still TODO's below.

# Details

## Structure

    function &go - core.php
        function callFeature - core.php
            function event - condition.php
    
## Analysis

TODO Update the test to work out anyone's data.

Do `mass -vvvvv --testIf` and search for the output `[debug3]: debugResultSet notIfEmptyResult/2`. You'll see the count is 4. Soon below it you'll see

    [debug3]: GOT HERE                                                                                                                                                                              
    [debug5]: GOT HERE ALSO                                                                                                                                                                         

The first one is just before the return of `callFeature`, the second line is just after `callFeature` was called.

Next you'll see `[debug3]: debugResultSet testIf - notIfEmptyResult/2` and the count is now 1. It should be 4.

## Solution

There were two parts to the problem:

* The "memory bug" where I had forgotten to decrement the nesting in getParentResultSet()
* The issue described above where the shared memory had the correct data before exiting callFeature(), but lost it once back inside go(), directly after the return. I have yet to understand this, but putting it inside a condition that only sets the return value of go() if the value is !==false fixes it. I suspect this is a problem with PHP optimization.

## Cleanup

* A work-around was made: `workAroundIfBug` defined in core.php.
* Several ->debug lines. Some of these (particularly "GOT HERE") will need to either be changed to something more meaningful, or removed.
* Some debugging is invasive to performance. Either remove it, or make it only run once the appropriate level of verbosity is reached.
* TODO comments