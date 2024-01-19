<?php

namespace Netdust\Service\Scripts;

use lucatume\DI52\Container;
use Netdust\Utils\Logger\Logger;
use Netdust\Utils\DependencyRegistry;
use Netdust\Utils\Logger\LoggerInterface;


class ScriptRegistry extends DependencyRegistry {

    public function __construct(Container $container, array $instanceClass )
    {
        if( $this->is_valid( end($instanceClass) ) ) {
            parent::__construct($container, $instanceClass);
        }
    }

    public function get( string|array $id ): mixed {
        if( is_array($id) ) {
            $instances = [];
            foreach ( $id as $style ){
                $instances[] = $this->get( $style );
            }
            return $instances;
        }

        return $this->container->get( $id );
    }

    public function enqueue( $handle ): bool {
        $script = $this->get( $handle );
        $script->enqueue();
        return true;
    }

    protected function is_valid( $instanceClass ): bool|\WP_Error {
        if( ! in_array( ScriptInterface::class, class_implements($instanceClass) ) ) {
            return app()->make( LoggerInterface::class )->error(
                'The specified script could not be added because it doesnt implement ScriptInterface.',
                'script_not_valid'
            );
        }

        return true;
    }

}