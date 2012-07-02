# Description

Results get lost after the second `ifResult` in testIf.macro. 

# Status

Active. Currently being worked around using `workAroundIfBug` defined in core.php.

# Details

## Structure

    function &go - core.php
        function triggerEvent - core.php
            function event - condition.php
    
## Analysis

TODO Update the test to work out anyone's data.

Do `mass -vvvvv --testIf` and search for the output `[debug3]: debugSharedMemory notIfEmptyResult/2`. You'll see the count is 4. Soon below it you'll see

    [debug3]: GOT HERE                                                                                                                                                                              
    [debug5]: GOT HERE ALSO                                                                                                                                                                         

The first one is just before the return of `triggerEvent`, the second line is just after `triggerEvent` was called.

Next you'll see `[debug3]: debugSharedMemory testIf - notIfEmptyResult/2` and the count is now 1. It should be 4.

