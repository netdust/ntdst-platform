<?php

namespace Netdust\View;



use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Logger\Logger;


class TemplateServiceProvider extends ServiceProvider {


    public function register( ) {

        $app = $this->container->get(ApplicationInterface::class);

        $this->container->singleton( Engine::class );

        $app->mixin('get_template', function(string $layout, array $data = array() ) use ( $app ): string {
            $template = $app->get(Engine::class)->make( $app->template_dir( ) );
            return $template->render( $layout, $data );
        });

    }
}