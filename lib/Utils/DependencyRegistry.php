<?php

namespace Netdust\Utils;

use lucatume\DI52\Container;

use Netdust\Traits\Features;
use Netdust\Traits\Setters;
use Netdust\Vormingen\Services\Learndash\VAD_Learndash_API;

/**
 * Class DependencyRegistry
 *
 * Helpers class to bind classes to their implementation.
 *
 *
 *
 * @since   1.0.0
 */

class DependencyRegistry {

    protected $container;
    protected $instanceClass;

    public function __construct(Container $container, Array $instanceClass )
    {
        $this->container = $container;
        $this->instanceClass = end($instanceClass);
    }

    public function get( $id ) {
        return $this->container->get( $id );
    }

    public function add( $id, $args = [] ) {

        // make sure the constructor gets the arguments when needed
        $this->container->when( $id )->needs( '$args' )->give( $args );

        if( !key_exists('singleton', $args ) || !$args['singleton'] ) {
            $this->bind($id, $args);
        }
        else {
            $this->bindSingleton( $id, $args );
        }

        unset( $args['singleton'] );
        unset( $args['middlewares'] );

        /*
        if( in_array(Setters::class, class_uses($this->instanceClass) ) && count($args)>0 ) {
            $this->container->get($id)->set( $args );
        }*/

        if( in_array(Features::class, class_uses($this->instanceClass)) ) {
            $this->container->get($id)->do_actions();
        }

    }

    protected function bind( $id, $args ) {

        if( key_exists('middlewares', $args ) ) {
            $args['middlewares'][] = $this->instanceClass;
            $this->container->bindDecorators($id, $args['middlewares'] );
        }
        else {
            $this->container->bind($id, $this->instanceClass);
        }

    }

    protected function bindSingleton( $id, $args ) {

        if( key_exists('middlewares', $args ) ) {
            $args['middlewares'][] = $this->instanceClass;
            $this->container->singletonDecorators($id, $args['middlewares'] );
        }
        else {
            $this->container->singleton($id, $this->instanceClass);
        }

    }

    public function __call( $method, $arguments ) {
        // If this method exists, bail and just get the method.
        if ( method_exists( $this, $method ) ) {
            return $this->$method( ...$arguments );
        }

        if ( method_exists( $instance = $this->container->get( $this->instanceClass ), $method ) ) {
            return $instance->$method( ...$arguments );
        }

        return new \WP_Error(
            'method_not_found',
            "The method could not be called. Either register this method as api, or create a method for this call.",
            [
                'method'    => $method,
                'args'      => $arguments,
                'backtrace' => debug_backtrace(),
            ]
        );
    }

}