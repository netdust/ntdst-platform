<?php

namespace Netdust;


use lucatume\DI52\ServiceProvider;
use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use Netdust\Service\Blocks\ACFBlock;
use Netdust\Service\Posts\Post;
use Netdust\Service\Posts\Taxonomy;
use Netdust\Service\Scripts\Script;
use Netdust\Service\Styles\Style;
use Netdust\Service\Users\Role;
use Netdust\Traits\Features;


class WordpressServiceProvider extends ServiceProvider {

    public function register( ) {


        $app = $this->container->get(ApplicationInterface::class);

        /**
         * add wp_register mixin to have unified api for WordPress objects
         * use : app()->wp_register( 'videos', Post::class, [] );
         */
        $app->mixin('wp_register',  function( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null ) use ( $app ): mixed {

            $isAllowed = false;

            $allowedBaseClasses = [
                Style::class,
                Script::class,
                Post::class,
                Taxonomy::class,
                ACFBlock::class,
                Role::class,
            ];

            $className = $implementation;
            if (is_object($implementation)) {
                $className = get_class($implementation);
            }

            foreach ($allowedBaseClasses as $allowedClass) {
                if (is_subclass_of($className, $allowedClass) || $className === $allowedClass) {
                    $isAllowed = true;
                    break;
                }
            }

            if ( $isAllowed ) {
                if( method_exists( $implementation, 'do_actions' )  ) {
                    $afterBuildMethods = array_merge($afterBuildMethods??[], ['do_actions']);
                }
                if( did_action('init')>0 && method_exists( $implementation, 'register') ) {
                    $afterBuildMethods = array_merge($afterBuildMethods??[], ['register']);
                }

                return $app->make( $id, $implementation, $args, $afterBuildMethods );
            }

            $app->make( LoggerInterface::class )->warning(
                'The ' . $className . ' failed to register.',
                self::class
            );

            return false;
        });

    }


}