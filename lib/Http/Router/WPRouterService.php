<?php


namespace Netdust\Http\Router;


use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Core\File;
use Netdust\Logger\Logger;
use Netdust\Traits\Mixins;


class WPRouterService extends ServiceProvider
{

    public function register(): void
    {
        $this->container->singleton(
            RouterInterface::class,  Router::router( )
        );

    }

    public function boot(): void  {

    }
}