<?php

namespace Netdust\Core;

use lucatume\DI52\Container;
use Netdust\App;


use Netdust\ApplicationInterface;
use Netdust\ApplicationProvider;
use Netdust\Service\Posts\Post;
use Netdust\Service\Scripts\Script;
use Netdust\Service\Styles\Style;

abstract class ServiceProvider extends \lucatume\DI52\ServiceProvider {

    public function register() {

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