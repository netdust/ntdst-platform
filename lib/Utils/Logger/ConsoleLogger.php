<?php

namespace Netdust\Utils\Logger;


use Netdust\Traits\Decorator;
use Netdust\Utils\Logger\LoggerInterface;


class ConsoleLogger implements LoggerInterface
{

    use Decorator;
    public function __construct( $logger ) {
        $this->decorated = $logger;
    }

    public function info( $message, $name = '' ) {
        $log_entry = $this->decorated->info( $message, $name = '' );
        $this->output( $log_entry );
        return $log_entry;
    }
    public function debug( $message, $name = '' ){
        $log_entry = $this->decorated->debug( $message, $name = '' );
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
        if( ! empty( $log_entry ) ) {
            $message = json_encode( $log_entry['message'] );

            $llevel = $log_entry['level'];
            if( $log_entry['level']=='info' || $log_entry['level']== "debug" )
                $llevel = 'log';
            if( $log_entry['level']=='warning' )
                $llevel = 'warn';

            echo PHP_EOL . "<script type='text/javascript'> console.{$llevel}({$message}); </script>";
        }
    }
}