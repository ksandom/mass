Included this since I don't have this package in linux and the parameters are different to clusterssh, which I do have.

Usage:
    csshX [--login *username*] [--config *filename*] [ *[user@]host1[:port]*
    [*[user@]host2[:port]*] .. ]

    csshX [-h | -m | -v ]

Options:
    -l *username*, --login *username*
        Remote user to authenticate as for all hosts. This is overridden by
        *user@*.

    -c *configfile*, --config *configfile*
        Alternative config file to use

    -h, --help
        Quick summary of program usage

    -m, --man
        Full program man page

    -v, --version                                                                                                                                                                                           
        Displays the version of csshX                                                                                                                                                                       
                                                                                                                                                                                                            
    --screen *number or range*                                                                                                                                                                              
        Sets the screen(s) on which to display the terminals, if you have
        multiple monitors. If the argument is passed a number, that screen
        will be used.

        If a range (of the format 1-2) is passed, a rectangle that fits
        within those displays will be chosen. Particularly odd arrangements
        of windows, such as "L" shapes will probably not work.

        Screens are numbered from 1.

    --space *number*
        Sets the space (if Spaces is enabled) on which to display the
        terminals.

        Default: *0* (current space)

    -x, --tile_x *number*
        (csshX only) The number of columns to use when tiling windows.

    -y, --tile_y *number*
        (csshX only) The number of rows to use when tiling windows. tile_x
        will be used if both are specified.

    --ssh *ssh command*
        Change the command that is run. May be useful if you use an
        alternative ssh binary or some wrapper script to connect to hosts.

    --ssh_args *ssh arguments*
        Sets a list of arguments to pass to the ssh binary when run. If
        there is more than one, they must be quoted or escaped to prevent
        csshX from interpreting them.

    --remote_command *command to run*
        Sets the command to run on the remote system after authenticating.
        If the command contains spaces, it should be quoted or escaped.

        To run different commands on different hosts, see the --hosts
        option.

    --hosts *hosts_file*
        Load a file containing a list of hostnames to connect to and,
        optionally, commands to run on each host. A single dash - can be
        used to read hosts data from standard input, for example, through a
        pipe.

        See HOSTS for the file format.

    --session_max *number*
        Set the maximum number of ssh Terminal sessions that can be opened
        during a single csshX session. By default csshX will not open more
        than 256 sessions. You must set this to something really high to get
        around that. (default: 256)

        Note that you will probably run out of Pseudo-TTYs before reaching
        256 terminal windows.

    --ping_test, --ping *number*
        To avoid opening connections to machines that are down, or not
        running sshd, this option will make csshX ping each host/port that
        is specified. This uses the Net::Ping module to perform a simple
        syn/ack check.

        Use of this option is highly recommended when subnet ranges are
        used.

    --ping_timeout *number*
        This sets the timeout used when the "ping_test" feature is enabled.

        Due to the implementation of Net::Ping syn/ack checks, this timeout
        applies once per destination port used. Also, if the number of hosts
        to ping is greater than the number of filehandles available pings
        will be batched, and the timeout will apply once per batch. You can
        set 'ulimit -n' to improve this performance.

        The value is in seconds. (default: 2)

    --sock *sockfile*
        Sets the Unix domain socket filename to be used for interprocess
        communication. This may be set by the user in the launcher session,
        possibly for security reasons.

    --sorthosts
        Sort the host windows, by hostname, before opening them.

    --slave_settings_set, --sss *string*
        Change the "settings set" for slave windows. See slave_settings_set
        below for an explanation of why you might do this.

    --master_settings_set, --mss *string*
        Change the "settings set" for master windows.

    -i, --interleave *number*
        (csshX only) Interleave the hosts that were passed in. Useful when
        multiple clusters are specified.

        For instance, if clusterA and clusterB each have 3 hosts, running
        csshX -tile_x 2 -interleave 3 clusterA clusterB

        will display as clusterA1 clusterB1 clusterA2 clusterB2 clusterA3
        clusterB3

        as opposed to the default clusterA1 clusterA2 clusterA3 clusterB1
        clusterB2 clusterB3

    --debug *number*
        Sets the debug level. Number is optional and will default to 1 if
        omitted.

        Currently only one level of debug is supported. It will enable
        backtrace on fatal errors, and will keep terminal windows open after
        terminating (so you can see any errors).
