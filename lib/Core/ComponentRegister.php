<?php

namespace Netdust\Core;


use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\ApplicationProvider;
use Netdust\Logger\Logger;


class ComponentRegister {

    protected Factory $factory;
    protected Config $config;

    public function __construct( Factory $factory, Config $config ) {
        $this->factory = $factory;
        $this->config = $config;
    }

    public function registerAll(): void
    {
        $this->registerBlocks( $this->config );
        $this->registerPatterns( $this->config );
        $this->registerShortcodes( $this->config );
        $this->registerUserRoles( $this->config );
        $this->registerPostTypesAndTaxonomies( $this->config );
        $this->registerScriptStyles( $this->config );
        $this->registerAdminPages( $this->config );
    }

    public function registerBlocks( $config ) {
        foreach( ($config['blocks']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['block_name'], $build, $args,[], true );
            }
        }
    }

    public function registerPatterns( $config ) {
        foreach( ($config['patterns']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['type'], $build, $args,[], true );
            }
        }
    }

    public function registerShortcodes( $config ) {
        foreach( ($config['shortcodes']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['tag'], $build, $args, ['do_actions'], true );
            }
        }
    }

    public function registerUserRoles( $config ) {
        foreach( ($config['roles']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['role'], $build, $args, ['do_actions'], true );
            }
        }
    }


    public function registerPostTypesAndTaxonomies( $config ) {
        foreach( ($config['posts']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['type'], $build, $args, ['do_actions'], true );
            }
        }
        foreach( ($config['taxonomies']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['taxonomy'], $build, $args, ['do_actions'], true );
            }
        }
    }

    public function registerScriptStyles( $config ) {

        foreach( ($config['styles']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $asset = $this->factory->make( $args['handle'], $build, $args,[],true)
                    ->setDependencies( $args['dependencies'] ?? [] )
                    ->setAttributes( $args['attributes'] ?? [] )
                    ->setMedia( $args['media'] ?? '' )
                    ->to( $args['to'] ?? [] );

                $asset->register();
            }
        }

        foreach( ($config['scripts']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $asset = $this->factory->make( $args['handle'], $build, $args,[],true)
                    ->setDependencies( $args['dependencies'] ?? [] )
                    ->setAttributes( $args['attributes'] ?? [] )
                    ->setInFooter( $args['footer'] ?? true )
                    ->to( $args['to'] ?? [] );

                if( isset( $args['localized'] ) ) {
                    $asset->setLocalizedVar( $args['localized'][0], $args['localized'][1] );
                }

                $asset->register();

            }
        }
    }

    public function registerAdminPages( $config ): void {
        foreach( ($config['admin']??[] )  as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['handle'], $build, $args, ['init'], true );
            }
        }
    }

}