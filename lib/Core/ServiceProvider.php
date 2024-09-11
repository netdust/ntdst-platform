<?php

namespace Netdust\Core;

use Netdust\App;


use Netdust\ApplicationInterface;
use Netdust\Service\Posts\Post;
use Netdust\Service\Scripts\Script;
use Netdust\Service\Styles\Style;

abstract class ServiceProvider extends \lucatume\DI52\ServiceProvider {


    public function register() {
		/*
	    $this->container->bind( WordPressController::class, function ( $c ) {
		    return new WordPressController( $c[ WPEMERGE_VIEW_SERVICE_KEY ] );
	    } );*/
    }

    /**
     * access to main ServiceProvider
     *
     * @return mixed
     */
    public function app( string $id = ApplicationInterface::class): mixed {
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