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
        $this->container->singleton(
            AdminNoticesService::class, new AdminNoticesService()
        );

        $this->container->singleton(
            LoggerInterface::class, new SimpleLogger()
        );
        Logger::setLogger( $this->container->get(LoggerInterface::class) );

    }

    public function boot(): void
    {

    }
}