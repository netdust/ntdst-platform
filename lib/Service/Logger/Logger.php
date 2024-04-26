<?php

namespace Netdust\Service\Logger;


/**
 * A facade to make a LoggerInterface instance globally available as a lmog service.
 *
 * @package Netdust\Utils\Logger
 */

class Logger {

    /** A reference to the singleton instance of the LoggerInterface
     * the application uses as log service.
     *
     * @var LoggerInterface|null
     */
    protected static  $logger;

    /**
     * Returns the singleton instance of the LoggerInterface the application
     * will use as a log service.
     *
     * @return LoggerInterface The singleton instance of the LoggerInterface
     */
    public static function logger( )
    {
        if (!isset(static::$logger)) {
            static::setLogger( new SimpleLogger() );
        }

        return static::$logger;
    }

    /**
     * Sets the logger instance the Application should use as a log Service
     *
     * @param LoggerInterface $logger A reference to the Container instance the Application
     *                             should use as a Service Locator.
     *
     * @return void The method does not return any value.
     */
    public static function setLogger(LoggerInterface $logger)
    {
        static::$logger = $logger;
    }
    
    
    /**
     * Add a log entry with a diagnostic message for the developer.
     */
    public static function debug( $message, $name = '' ) {
        return static::logger()->debug( $message, $name );
    }


    /**
     * Add a log entry with an informational message for the user.
     */
    public static function info( $message, $name = '' ) {
        return static::logger()->info( $message, $name );
    }


    /**
     * Add a log entry with a warning message.
     */
    public static function warning( $message, $name = '', $data=[] ) {
        return static::logger()->warning( $message, $name, $data );
    }


    /**
     * Add a log entry with an error - usually followed by
     * script termination.
     */
    public static function error( $message, $name = '', $data=[] ) {
        return static::logger()->error( $message, $name, $data );
    }


}