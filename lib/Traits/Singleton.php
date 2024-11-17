<?php

namespace Netdust\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


use Exception;
use Netdust\Factories\Logger;
use Netdust\Logger\LoggerInterface;

trait Singleton {

    /**
     * Holds references to the singleton instances.
     *
     */
    private static mixed $instance;


    /**
     * Get an instance of the class.
     */

    public static function instance(): mixed {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
