<?php

namespace Netdust\Service\Styles;

use lucatume\DI52\Container;

use Netdust\Utils\DependencyRegistry;
use Netdust\Utils\Logger\LoggerInterface;


class StyleRegistry extends DependencyRegistry {

    public function __construct(Container $container, Array $instanceClass )
    {
        if( $this->is_valid( end($instanceClass) ) ) {
            parent::__construct($container, $instanceClass);
        }
    }

    public function get( array|string $id ): mixed {
        if( is_array($id) ) {
            $instances = [];
            foreach ( $id as $style ){
                $instances[] = $this->get( $style );
            }
            return $instances;
        }

        return $this->container->get( $id );
    }

    public function enqueue( string $handle ): bool
    {
        $style = $this->get($handle);
        $style->enqueue();
        return true;
    }

    protected function is_valid( string $instanceClass ): bool|\WP_Error {
        if( ! in_array( StyleInterface::class, class_implements($instanceClass) ) ) {
            return app()->make( LoggerInterface::class )->error(
                'The specified style could not be added because it doesnt implement StyleInterface.',
                'style_not_added'
            );
        }

        return true;
    }

}