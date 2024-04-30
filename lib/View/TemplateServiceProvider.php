<?php

namespace Netdust\View;


use lucatume\DI52\ServiceProvider;


class TemplateServiceProvider extends ServiceProvider {

    public function register( ) {

        $container = $this->container;

        $container->singleton( Engine::class );

        $container->singleton('render', $container->protect(function() use ( $container ) {
            return call_user_func_array( [$container->make(Engine::class), 'render'], func_get_args() );
        }));

    }
}