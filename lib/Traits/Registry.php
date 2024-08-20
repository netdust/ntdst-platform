<?php

namespace Netdust\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use lucatume\DI52\Container;
use ReflectionClass;
use ReflectionMethod;

trait Registry {


    public function make( string $key, array $args, array $afterBuildMethods = [] ) {
        app()->container()->bind( $key, $this );

        if( in_array(Setters::class, class_uses($this)) ) {
            $this->set_values( $args );
        }

        if( in_array(Features::class, class_uses($this)) ) {
            $this->do_actions();
        }

        foreach ($afterBuildMethods as $afterBuildMethod) {
            $this->{$afterBuildMethod}();
        }
    }

}