This document is currently inactive, but the contents have been verified to be correct as at 2012-10-19.

# Background
While building the --importHosts macro I realised that the palette of data management functionality was no where near as complete as I thought it was. The purpose of this document is to find out what I do and don't have and provide a one stop shop for all things data management.

# Terms
## Store (eg --saveStoreToConfig)

Every module has it's own store, which can contain many variables. Eg **Host**,hostDefinitions. Saving or loading a store will do the action to the entire store including it's contents. Where possible use Data instead of Config since config is loaded on every run of mass. See below.

## Config vs Data (eg --loadStoreFromConfig and --loadStoreFromData)

When saving or loading a store, the resulting file can be stored in massHome/config or massHome/data.

* Config will be loaded each time mass is started.
* Data will only be loaded when someone/thing invokes --loadStoreFromData.

That's the only difference. The underlying files can be copied back and forth as long as you substitute config or data in the filename accordingly.

## File (eg --loadStoreFromFile)

This is a special case where you can import config copied from another install. This does not save it, it simply loads it into memory so you can save it where you want.

# Behavior of imports and exports

Unless specified otherwise on a per feature basis, an import does not save the imported data. This is so you can choose what you want to do with it. You can create a macro to combine these actions if that is useful to you. On the same note a save expects you to already have the data you want to save.

# Flow (import/export)
*Tags: Data*

