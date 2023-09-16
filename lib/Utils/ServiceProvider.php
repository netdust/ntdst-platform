<?php

namespace Netdust\Utils;

use lucatume\DI52\Container;
use Netdust\App;
use Netdust\Service\Posts\Post;
use Netdust\Service\Styles\Style;
use Netdust\Service\Scripts\Script;

abstract class ServiceProvider extends \lucatume\DI52\ServiceProvider {


    abstract public function register();

    /**
     * access to main ServiceProvider
     *
     * @return App|mixed
     */
    public function app( $id = NTDST_APPLICATION) {
        return $this->container->get( $id );
    }
    public function scripts() {
        return $this->container->get(Script::class);
    }
    public function styles() {
        return $this->container->get(Style::class);
    }

    public function posts() {
        return $this->container->get(Post::class);
    }

}