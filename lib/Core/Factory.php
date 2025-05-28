<?php

namespace Netdust\Core;

use lucatume\DI52\Container;


class Factory  {

    private Container $container;

    public function __construct(Container $container ) {
        $this->container = $container;
    }

    public function make( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null, bool $singleton = false ): mixed {

        if( !empty($args) && key_exists('decorators', $args ) ) {
            return $this->makeDecorators( $id, $implementation, $args, $afterBuildMethods, $singleton );
        }


        $this->buildArguments( $id, $implementation, $args );

        if(!empty($implementation) ) {

            if( !$singleton ) {
                $this->container->bind( $id, $implementation, $afterBuildMethods );
            }
            else {
                $this->container->singleton( $id, $implementation, $afterBuildMethods );
            }
        }

        return $this->container->get( $id );
    }

    public function makeDecorators( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null, bool $singleton = false ): mixed {

        if( !empty($args) && !key_exists('decorators', $args ) ) {
            $args['decorators'] = [];
        }

        $args['decorators'][] = $implementation;
        $this->buildArguments( $id, $implementation, $args );

        if( !$singleton ) {
            $this->container->bindDecorators($id, $args['decorators'], $afterBuildMethods, true );
        }
        else {
            $this->container->singletonDecorators($id, $args['decorators'], $afterBuildMethods, true );
        }

        return $this->container->get( $id );
    }

    protected function buildArguments( string $id, mixed $implementation = null, array $args = null ) {
        if(!empty($args) )  {

            $className = !empty($implementation) ? $implementation:$id;

            if( class_exists($className) ) {
                $constructor = ( new \ReflectionClass($className) )->getConstructor();
                $parameters = $constructor ? $constructor->getParameters() : [];

                foreach ($parameters as $parameter) {
                    if( key_exists($parameter->getName(),$args) ) {
                        $this->container->when( $id )->needs('$'.$parameter->getName() )->give( $args[$parameter->getName()] );
                    }
                    else if( $parameter->getName() == 'args' ) {
                        $this->container->when( $id )->needs('$args' )->give( $args );
                    }
                }
            }

        }
    }

}