<?php

namespace Netdust\Logger;

interface LoggerInterface {

    public function info( $message, $name = '' );
    public function debug( $message, $name = '' );
    public function warning( $message, $name = '', $data=[] );
    public function error( $message, $name = '', $data=[] );
    public function output( $log_entry );

}