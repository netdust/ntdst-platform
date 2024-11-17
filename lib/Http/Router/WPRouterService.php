<?php


namespace Netdust\Http\Router;


use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Core\File;
use Netdust\Traits\Mixins;


class WPRouterService extends ServiceProvider
{

    public function register(): void
    {
        $this->container->singleton(
            RouterInterface::class, new \Netdust\Http\Router\WPRouter()
        );
        Router::setRouter( $this->container->get(RouterInterface::class) );

    }

    public function boot(): void  {

    }
}