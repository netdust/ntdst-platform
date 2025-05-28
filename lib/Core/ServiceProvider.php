<?php

namespace Netdust\Core;

use Netdust\ApplicationInterface;
use Netdust\View\TemplateInterface;


abstract class ServiceProvider extends \lucatume\DI52\ServiceProvider {

    public function get( string $id ): mixed {
        return $this->container->get( $id );
    }

    public function app(): mixed {
        return $this->container->get( ApplicationInterface::class );
    }

    public function factory(): Factory {
        return $this->container->get( Factory::class );
    }

}