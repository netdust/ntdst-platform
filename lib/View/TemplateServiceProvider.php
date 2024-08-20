<?php

namespace Netdust\View;



use lucatume\DI52\ServiceProvider;


class TemplateServiceProvider extends ServiceProvider {


    public function register( ) {

        $container = $this->container;

        $container->singleton( Engine::class );

        app()->mixin('get_template', function(string $layout, array $data = array() ): string {
            $template = app()->container()->make(Engine::class)->make( app()->template_dir( ) );
            return $template->render( $layout, $data );
        });

    }
}