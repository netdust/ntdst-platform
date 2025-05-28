<?php


namespace Netdust\Logger;


use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\APIInterface;
use Netdust\ApplicationInterface;
use Netdust\Core\File;
use Netdust\Traits\Mixins;
use Netdust\Vormingen\Services\Fluent\VAD_Fluent_API;
use Netdust\Vormingen\Services\Fluent\VAD_Fluent_Company_API;
use Netdust\Vormingen\Services\Fluent\VAD_Fluent_Model;


class NotificationService extends ServiceProvider
{

    public function register(): void
    {
        // logger as singleton
        $this->container->singleton(
            LoggerInterface::class, new SimpleLogger()
        );
        Logger::setLogger( $this->container->get(LoggerInterface::class) );
        $this->container->get( ApplicationInterface::class )->mixin( 'logger', function(){
            return $this->container->get(LoggerInterface::class);
        } );

        // admin notification as singleton
        $this->container->singleton(AdminNoticesService::class );
        $this->container->get( ApplicationInterface::class )->mixin( 'notices', function(){
            return $this->container->get(AdminNoticesService::class);
        } );

    }

    public function boot(): void {

    }

}