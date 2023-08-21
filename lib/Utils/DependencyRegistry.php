<?php

namespace Netdust\Utils;

use lucatume\DI52\Container;

use Netdust\Traits\Features;
use Netdust\Traits\Setters;

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

        if( in_array(Setters::class, class_uses($this->instanceClass) ) && count($args)>0 ) {
            $this->container->get($id)->set( $args );
        }
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

}