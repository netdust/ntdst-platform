<?php

namespace Netdust\Core;

use Netdust\ApplicationInterface;
use Netdust\View\TemplateInterface;


abstract class ServiceProvider extends \lucatume\DI52\ServiceProvider {

    public function register()
    {

    }

    /**
     * access to main ServiceProvider
     *
     * @return mixed
     */
    public function app( string $id = ApplicationInterface::class): mixed {
        return $this->container->get( $id );
    }

}