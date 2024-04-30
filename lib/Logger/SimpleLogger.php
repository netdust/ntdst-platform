<?php

namespace Netdust\Logger;



/**
 * Simple logger class.
 *
 * Log entries can be added with any of the following methods:
 *  - Logger()->info( $message, $title = '' )      // an informational message intended for the user
 *  - Logger()->debug( $message, $title = '' )     // a diagnostic message intended for the developer
 *  - Logger()->warning( $message, $title = '' )   // a warning that something might go wrong
 *  - Logger()->error( $message, $title = '' )     // explain why the program is going to crash
 *
 * See README.md for examples and configuration.
 */
class SimpleLogger implements LoggerInterface {

    /**
     * Incremental log, where each entry is an array with the following elements:
     *
     *  - timestamp => timestamp in seconds as returned by time()
     *  - level => severity of the bug; one between debug, info, warning, error
     *  - name => name of the log entry, optional
     *  - message => actual log message
     */
    protected $log = [];

    /**
     * Set the maximum level of logging to write to logs
     */
    public $log_level = 'debug';

    /**
     * Name for the default timer
     */
    public $default_timer = 'timer';

    /**
     * add timestamp to log line
     */
    public $use_timestamp = false;

    /**
     * Map logging levels to syslog specifications, there's room for the other levels
     */
    private $log_level_integers = [
        'info' => 7,
        'debug' => 6,
        'warning' => 4,
        'error' => 3
    ];

    /**
     * Associative array used as a buffer to keep track of timed logs
     */
    private $time_tracking = [];


    /**
     * Add a log entry with a diagnostic message for the developer.
     */
    public function debug( $message, $name = '' ) {
        return $this->add( $message, $name, 'debug' );
    }


    /**
     * Add a log entry with an informational message for the user.
     */
    public function info( $message, $name = '' ) {
        return $this->add( $message, $name, 'info' );
    }


    /**
     * Add a log entry with a warning message.
     */
    public function warning( $message, $name = '', $data=[] ) {
        return $this->add( $message, $name, 'warning', $data );
        //return new \WP_Error( $name, __( $message, "ntdst" ), $data );
    }


    /**
     * Add a log entry with an error - usually followed by
     * script termination.
     */
    public function error( $message, $name = '', $data=[] ) {
        return $this->add( $message, $name, 'error', $data );
        //return new \WP_Error( $name, __( $message, "ntdst" ), $data );
    }


    /**
     * Start counting time, using $name as identifier.
     *
     * Returns the start time or false if a time tracker with the same name
     * exists
     */
    public function time( string $name = null ) {

        if ( $name === null ) {
            $name = $this->default_timer;
        }

        if ( ! isset( $this->time_tracking[ $name ] ) ) {
            $this->time_tracking[ $name ] = microtime( true );
            return $this->time_tracking[ $name ];
        }
        else {
            return false;
        }
    }


    /**
     * Stop counting time, and create a log entry reporting the elapsed amount of
     * time.
     *
     * Returns the total time elapsed for the given time-tracker, or false if the
     * time tracker is not found.
     */
    public function timeEnd( string $name = null, int $decimals = 6, $level = 'debug' ) {

        $is_default_timer = $name === null;

        if ( $is_default_timer ) {
            $name = $this->default_timer;
        }

        if ( isset( $this->time_tracking[ $name ] ) ) {
            $start = $this->time_tracking[ $name ];
            $end = microtime( true );
            $elapsed_time = number_format( ( $end - $start), $decimals );
            unset( $this->time_tracking[ $name ] );
            if ( ! $is_default_timer ) {
                $this->add( "$elapsed_time seconds", "Elapsed time for '$name'", $level );
            }
            else {
                $this->add( "$elapsed_time seconds", "Elapsed time", $level );
            }
            return $elapsed_time;
        }
        else {
            return false;
        }
    }


    /**
     * Add an entry to the log.
     *
     * This function does not update the pretty log.
     */
    private function add( $message, $name = '', $level = 'debug', $data=[] ) {

        /* Check if the logging level severity warrants writing this log */
        if ( $this->log_level_integers[$level] > $this->log_level_integers[$this->log_level] ){
            return;
        }

        /* Create the log entry */
        $log_entry = [
            'timestamp' => time(),
            'name' => $name,
            'message' => $message,
            'level' => $level,
            'data' => $data
        ];

        /* Add the log entry to the incremental log */
        $this->log[] = $log_entry;

        $this->output( $log_entry );

        return $log_entry;
    }

    public function output( $log_entry ){
        $output_line = $this->format_log_entry( $log_entry );
        error_log( $output_line );
    }


    /**
     * Take one log entry and return a one-line human readable string
     */
    public function format_log_entry( array $log_entry ) : string {

        $log_line = "";

        if ( ! empty( $log_entry ) ) {

            if( is_array( $log_entry['data'] ) && count( $log_entry['data'] ) == 0 ) {
                unset( $log_entry['data'] );
            }
            /* Make sure the log entry is stringified */
            $log_entry = array_map( function( $v ) { return print_r( $v, true ); }, $log_entry );

            /* Build a line of the pretty log */
            if( $this->use_timestamp )
            $log_line .= date( 'c', $log_entry['timestamp'] ) . " ";
            $log_line .= "[" . strtoupper( $log_entry['level'] ) . "] : ";
            if ( ! empty( $log_entry['name'] ) ) {
                $log_line .= $log_entry['name'] . " => ";
            }
            $log_line .= $log_entry['message'];
            if (  ! empty( $log_entry['data'] )  ) {
                $log_line .= "\n" . $log_entry['data'];
            }

        }

        return $log_line;
    }

    /**
     * Dump the whole log to string, and return it.
     *
     * The method format_log_entry() is used to format the log.
     */
    public function dump_to_string() {

        $output = '';

        foreach ( $this->log as $log_entry ) {
            $log_line = $this->format_log_entry( $log_entry );
            $output .= $log_line . PHP_EOL;
        }

        return $output;
    }

    /**
     * Empty the log
     */
    public function clear_log() {
        $this->log = [];
    }

}