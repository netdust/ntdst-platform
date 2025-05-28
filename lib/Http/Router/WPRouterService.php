<?php


namespace Netdust\Http\Router;


use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Core\File;
use Netdust\Logger\AdminNoticesService;
use Netdust\Logger\Logger;
use Netdust\Traits\Mixins;


class WPRouterService extends ServiceProvider
{

    public function register(): void
    {
        $this->container->singleton(
            RouterInterface::class,  new WPRouter( )
        );
        Router::setRouter( $this->container->get( RouterInterface::class ) );
        $this->container->get( ApplicationInterface::class )->mixin( 'router', function(){
            return $this->container->get(RouterInterface::class);
        } );

    }

}