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
        $this->container->tag(  [
            LoggerInterface::class,
            AdminNoticesService::class,
        ], 'notification_services');

        // logger as singleton
        $this->container->singleton(
            LoggerInterface::class, new SimpleLogger()
        );
        Logger::setLogger( $this->container->get(LoggerInterface::class) );


        // admin notification as singleton
        $this->container->singleton(
            AdminNoticesService::class, new AdminNoticesService()
        );

    }

    public function boot(): void {

    }

    public function __call( $method, $parameters ): mixed {

        foreach (  $this->container->tagged('notification_services') as $service ) {
            if(  method_exists( $service, $method ) ) {
                return $service->$method( ...$parameters );
            }
        }

        return new \WP_Error(
            'method_not_found',
            "The method could not be called. Either register this method as api, or create a method for this call.",
            [
                'method'    => $method,
                'args'      => $parameters
            ]
        );
    }
}