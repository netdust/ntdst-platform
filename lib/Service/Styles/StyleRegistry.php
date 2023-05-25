<?php

namespace Netdust\Service\Styles;

use lucatume\DI52\Container;
use Netdust\Utils\Logger\Logger;
use Netdust\Utils\DependencyRegistry;


class StyleRegistry extends DependencyRegistry {

    public function __construct(Container $container, Array $instanceClass )
    {
        if( $this->is_valid( end($instanceClass) ) ) {
            parent::__construct($container, $instanceClass);
        }
    }

    public function get( $id ) {
        if( is_array($id) ) {
            $instances = [];
            foreach ( $id as $style ){
                $instances[] = $this->get( $style );
            }
            return $instances;
        }

        return $this->container->get( $id );
    }

    public function enqueue( $handle )
    {
        $style = $this->get($handle);
        $style->enqueue();
        return true;
    }

    protected function is_valid( $instanceClass ) {
        if( ! in_array( StyleInterface::class, class_implements($instanceClass) ) ) {
            $error = Logger::error(
                'The specified style could not be added because it doesnt implement StyleInterface.',
                'style_not_added'
            );
            throw new \Exception($error->get_error_message());
        }

        return true;
    }

}