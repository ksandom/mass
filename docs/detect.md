--detect uses a seed within the scope of a module name to run a series of tests eg ExampleModule,seed. A seed looks like this:

    thing1,thing2,thing3

One we have that seed, we then look for specific values for each item. For example:

* thing1Name - A short no-spaces title. Eg `konsole`
* thing1Description - A more full description of the entry. Eg `KDE's native terminal emulator`
* thing1Group - A prefix to be given to the resulting detail keys. Eg `GUI`
* thing1test - A shell command that will return success of failure. Eg `which konsole`
* thing1cmd - What would be executed when ever this entry needs to be run assuming it gets selected. Eg `konsole`
* thing1parms - Any extra parameters it needs to run correctly. Eg `-e`

The selected item will
