<?php

namespace Netdust\Core;

use lucatume\DI52\Container;

use Netdust\Traits\Features;
use Netdust\Traits\Setters;
use Netdust\Utils\Logger\LoggerInterface;
use Netdust\Vormingen\Services\Learndash\VAD_Learndash_API;

/**
 * Class ObjectFactory
 *
 * Factory class to bind classes to their implementation.
 *
 * @since   1.0.0
 */

class ObjectFactory {

    protected $container;
    /**
     * Implementation that will be used for this factory class
     * @var string
     */
    protected string $instanceClass;

    public function __construct(Container $container, string $instanceClass )
    {
        $this->container = $container;
        $this->instanceClass = $instanceClass;
    }

    public function get( string $id ): mixed {
        return $this->container->get( $id );
    }

    /**
     * Builder method to Bind an interface, a class or a string slug to an implementation.
     *
     * @param string             $id                A class or interface fully qualified name or a string slug.
     * @param array              $args              key, value pair with constructor arguments
     * @param array<string>|null $decorators        An array of instances that will be used as decorators
     *                                              could be used to implement middleware
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return void The method does not return any value.
     */
    public function add( string $id, array $args = [], array $decorators = null, array $afterBuildMethods = null, bool $singleton = false ): void {

        // make sure the constructor gets the arguments when needed
        foreach ( $args as $key => $param ) {
            $this->container->when( $id )->needs( $key )->give( $param );
        }

        if( !empty($args) && $decorators!==null ) {
            $decorators[] = $this->instanceClass;
            if( !$singleton ) $this->container->bindDecorators($id, $decorators, $afterBuildMethods );
            else $this->container->singletonDecorators($id, $decorators, $afterBuildMethods );
        }
        else {
            if( !$singleton ) $this->container->bind($id, $this->instanceClass, $afterBuildMethods);
            else $this->container->singleton($id, $this->instanceClass, $afterBuildMethods);
        }

    }


    public function __call( $method, $arguments ): mixed {

        // If this method exists, bail and just get the method.
        if ( method_exists( $this, $method ) ) {
            return $this->$method( ...$arguments );
        }

        if ( method_exists( $instance = $this->container->get( $this->instanceClass ), $method ) ) {
            return $instance->$method( ...$arguments );
        }

        return app()->make( LoggerInterface::class )->error(
            'The method could not be called.',
            'method_not_found'
        );

    }

}