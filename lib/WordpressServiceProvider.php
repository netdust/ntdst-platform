<?php

namespace Netdust;


use lucatume\DI52\ServiceProvider;
use Netdust\Service\Scripts\Script;
use Netdust\Service\Styles\Style;


class WordpressServiceProvider extends ServiceProvider {

    public function register( ) {

        $container = $this->container;


        $container->singleton('enqueue', $container->protect(function( string $handle  ) use ( $container )
        {
            $instance = $container->get($handle);
            if ( method_exists( $instance, 'enqueue' ) ) {
                return $instance->enqueue();
            }

            return false;
        }));


        $container->singleton('add_style', $container->protect(function( string $id, array $args, ?array $afterBuildMethods = null  ) use ( $container )
        {
            return App()->make( $id, Style::class,  $args, array_merge( ['do_actions'] , $afterBuildMethods??[] ) );
        }));

        $container->singleton('add_script', $container->protect(function( string $id, array $args, ?array $afterBuildMethods = null  ) use ( $container )
        {
            return App()->make( $id, Script::class, $args, array_merge( ['do_actions'] , $afterBuildMethods??[] )  );
        }));

        $container->singleton('setup_shortcode', $container->protect(function( string $id, mixed $implementation = null, ?array $args = null, ?array $afterBuildMethods = null  ) use ( $container )
        {
            $container->when( $id )->needs('$args' )->give( $args );
            $container->when( $id )->needs('$shortcode' )->give( $id );
            $container->bind( $id, $implementation, array_merge( ['do_actions'] , $afterBuildMethods??[] ) );
            return $container->get( $id );
        }));

    }


}