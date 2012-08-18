# --detect
--detect uses a seed within the scope of a module name to run a series of tests eg ExampleModule,seed. A seed looks like this:

    thing1:thing2:thing3

One we have that seed, we then look for specific values for each item. For example:

* thing1Name - A short no-spaces title. Eg `konsole`
* thing1Description - A more full description of the entry. Eg `KDE's native terminal emulator`
* thing1Group - A prefix to be given to the resulting detail keys. Eg `GUI`
* thing1Test - A shell command that will return success of failure. Eg `which konsole`
* thing1CMD - What would be executed when ever this entry needs to be run assuming it gets selected. Eg `konsole`

The selected item will get sent to the same Category as each item with the seedName replaced by the groupName like so:

TODO this example is outdated. Update it. For now check out createDefaultValues.macro for the latest details.

* groupNameName Eg GUIName => `konsole`
* groupNameDescription Eg GUIDescription => `KDE's native terminal emulator`
* groupNameCmd Eg GUICmd => `konsole`
