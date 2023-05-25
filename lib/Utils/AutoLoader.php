<?php

namespace Netdust\Utils;

class AutoLoader
{
    private static $prefixes = [];

    public static $root_namespace = "Netdust";

    public static function addPrefix( $prefix, $base_directory ) {
        if( is_array( $prefix ) ) {
            foreach ( $prefix as $namepace => $dir )
                self::addPrefix( $namepace, $dir );
        } else {
            //$prefix = trim( $prefix, '\\' ) . '\\';
            $base_directory = rtrim( $base_directory, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
            self::$prefixes[] = [ $prefix, $base_directory ];
        }
    }

    public static function setup_autoloader( $prefix , $base_directory='lib/' ) {

        $prefix = $prefix ?? self::$root_namespace;
        self::addPrefix( $prefix, $base_directory );

        try {
            spl_autoload_register( function ( $class ) {

                if (!str_starts_with($class, 'Netdust')) {
                    //return false;
                }

                $file = self::find_file( $class );

                // If the file exists, use it.
                if ( file_exists( $file ) ) {
                    require_once $file;
                    return true;
                }

                return false;

            } );

        } catch ( \Exception $e ) {
            return $e->getMessage();
        }

        return false;
    }


    private static function find_file( $class )  {
        $class = ltrim( $class, '\\' );

        foreach ( self::$prefixes as $current ) {
            [ $prefix, $base_dir ] = $current;

            if ( 0 === strpos( $class, $prefix ) ) {

                //$prefix = trim( $prefix, '\\' ) . '\\';

                $name = ( $class !== $prefix ) ? substr( $class, \strlen( $prefix ) ) : $class;
                $file = $base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $name ) . '.php';

                if ( file_exists( $file ) ) {
                    return $file;
                }
            }
        }
        return null;
    }
}