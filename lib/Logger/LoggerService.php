<?php


namespace Netdust\Logger;


use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Core\File;
use Netdust\Traits\Mixins;


class LoggerService extends ServiceProvider
{

    public function register(): void
    {
        $this->container->register( AdminNoticesService::class, 'admin_notices' );

        $this->container->singleton(
            LoggerInterface::class, new SimpleLogger()
        );
        Logger::setLogger( $this->container->(LoggerInterface::class) );
        
    }

    public function boot(): void
    {

    }
}