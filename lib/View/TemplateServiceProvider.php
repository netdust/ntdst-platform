<?php

namespace Netdust\View;



use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Logger\Logger;
use Netdust\Traits\Mixins;


class TemplateServiceProvider extends ServiceProvider {


    public function register( ) {

        $this->container->singleton( Engine::class );

        app()->mixin('add_template_service', function( ServiceProvider $service, string $path = '/templates' ): void {
            $this->template_mixin( $service, $path );
        });

        $this->template_mixin( app(), app()->template_dir() );

    }

    public function template_mixin( ServiceProvider $service, string $path = '/templates' ): void {

        if ( !in_array(Mixins::class, class_uses($service), true) ) {
            throw new LogicException('The ServiceProvider is not using the mixins Trait.');
        }

        $service->mixin( 'get_template', function(string $layout, array $data = array() ) use ( $path ): string {
            $template = app()->get(Engine::class)->make( $path );
            return $template->render( $layout, $data );
        });
    }
}