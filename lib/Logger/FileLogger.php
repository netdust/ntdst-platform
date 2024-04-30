<?php

namespace Netdust\Logger;


use Netdust\Traits\Decorator;


class FileLogger implements LoggerInterface {

    use Decorator;

    public $output_stream;
    /**
     * Whether to write log entries to file as they are added.
     */
    public $write_log = true;
    /**
     * File name for the log saved in the log dir
     */
    public $log_file_name = "debug";

    /**
     * File extension for the logs saved in the log dir
     */
    public $log_file_extension = "log";

    /**
     * Whether to append to the log file (true) or to overwrite it (false)
     */
    public $log_file_append = true;
    /**
     * Directory where the log will be dumped, without final slash; default
     * is this file's directory
     */
    public $log_dir = __DIR__;
    /**
     * Absolute path of the log file, built at run time
     */
    private $log_file_path = '';

    public function __construct( $logger, $log_dir = __DIR__ ) {
        $this->decorated = $logger;
        $this->log_dir = $log_dir;

        /* Build log file path */
        if ( file_exists( $this->log_dir ) ) {
            $this->log_file_path = implode( DIRECTORY_SEPARATOR, [ $this->log_dir, $this->log_file_name ] );
            if ( ! empty( $this->log_file_extension ) ) {
                $this->log_file_path .= "." . $this->log_file_extension;
            }
        }

        if ( true === $this->write_log ) {
            if ( file_exists( $this->log_dir ) ) {
                $mode = $this->log_file_append ? "a" : "w";
                $this->output_stream = fopen ( $this->log_file_path, $mode );
            }
        }
    }

    public function info( $message, $name = '' ) {
        $log_entry = $this->decorated->info( $message, $name );
        $this->output( $log_entry );
        return $log_entry;
    }
    public function debug( $message, $name = '' ){
        $log_entry = $this->decorated->debug( $message, $name );
        $this->output( $log_entry );
        return $log_entry;
    }
    public function warning( $message, $name = '', $data=[] ){
        $log_entry = $this->decorated->warning( $message, $name, $data );
        $this->output( $log_entry );
        return $log_entry;
    }
    public function error( $message, $name = '', $data=[] ){
        $log_entry = $this->decorated->error( $message, $name, $data );
        $this->output( $log_entry );
        return $log_entry;
    }

    public function output( $log_entry ){
        if( !empty( $log_entry ) ) {
            $output_line = $this->format_log_entry( $log_entry ) . PHP_EOL;
            fputs( $this->output_stream, $output_line );
        }
    }

    /**
     * Dump the whole log to the given file.
     *
     * Useful if you don't know before-hand the name of the log file. Otherwise,
     * you should use the real-time logging option, that is, the $write_log or
     * $print_log options.
     *
     * The method format_log_entry() is used to format the log.
     *
     * @param {string} $file_path - Absolute path of the output file. If empty,
     * will use the class property $log_file_path.
     */
    public function dump_to_file( $file_path='' ) {

        if ( ! $file_path ) {
            $file_path = $this->log_file_path;
        }

        if ( file_exists( dirname( $file_path ) ) ) {

            $mode = $this->log_file_append ? "a" : "w";
            $output_file = fopen( $file_path, $mode );

            foreach ( $this->log as $log_entry ) {
                $log_line = $this->format_log_entry( $log_entry );
                fwrite( $output_file, $log_line . PHP_EOL );
            }

            fclose( $output_file );
        }
    }

}