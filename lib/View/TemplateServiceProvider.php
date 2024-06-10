<?php

namespace Netdust\View;



use lucatume\DI52\ServiceProvider;


class TemplateServiceProvider extends ServiceProvider {


    public function register( ) {

        $container = $this->container;

        $container->singleton( Engine::class );

        $container->singleton('template', new class {
           public function render(string $layout, array $data = array() ): string {
               return app()->container()->make(Engine::class)->render( $layout, $data );
           }

       });

       //app()->view_environment( dirname(__FILE__, [] ) )->get_template( 'email/default', [] );

    }
}