<?php

namespace Netdust;


use lucatume\DI52\ServiceProvider;
use Netdust\Logger\Logger;
use Netdust\Service\Blocks\ACFBlock;
use Netdust\Service\Posts\Post;
use Netdust\Service\Posts\Taxonomy;
use Netdust\Service\Scripts\Script;
use Netdust\Service\Styles\Style;


class WordpressServiceProvider extends ServiceProvider {

    public function register( ) {

        $container = $this->container;


        $container->singleton('wp_register', new class {

            protected array $allowed = [
                'style'=>Style::class,
                'script'=>Script::class,
                'post'=>Post::class,
                'taxonomy'=>Taxonomy::class,
                'block'=>ACFBlock::class,
            ];

            public function __call( $method, $arguments ): mixed {
                if ( array_key_exists( $method, $this->allowed ) ) {
                    $build_methods = array_merge( ( did_action('init')>0 ? ['register'] : ['do_actions'] ) , $arguments[2]??[] );
                    return App()->make( $arguments[0], $this->allowed[$method],  $arguments[1], $build_methods );
                }

                return false;
            }

        });


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