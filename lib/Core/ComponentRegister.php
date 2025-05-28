<?php

namespace Netdust\Core;


use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\ApplicationProvider;


class ComponentRegister {

    protected Factory $factory;
    protected Config $config;

    public function __construct( Factory $factory, Config $config ) {
        $this->factory = $factory;
        $this->config = $config;
    }

    public function registerAll(): void
    {
        $this->registerBlocks();
        $this->registerPatterns();
        $this->registerShortcodes();
        $this->registerUserRoles();
        $this->registerPostTypesAndTaxonomies();
        $this->registerScriptStyles();
    }

    public function registerBlocks() {
        foreach( ($this->config['blocks']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['block_name'], $build, $args,[], true );
            }
        }
    }

    public function registerPatterns() {
        foreach( ($this->config['patterns']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['type'], $build, $args,[], true );
            }
        }
    }

    public function registerShortcodes() {
        foreach( ($this->config['shortcodes']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['tag'], $build, $args, ['do_actions'], true );
            }
        }
    }

    public function registerUserRoles() {
        foreach( ($this->config['roles']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['role'], $build, $args, ['do_actions'], true );
            }
        }
    }


    public function registerPostTypesAndTaxonomies() {
        foreach( ($this->config['posts']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['type'], $build, $args, ['do_actions'], true );
            }
        }
        foreach( ($this->config['taxonomies']??[]) as $build => $module ) {
            $module = is_array( reset($module) ) ? $module : [$module];
            foreach($module as $args ) {
                $this->factory->make( $args['taxonomy'], $build, $args, ['do_actions'], true );
            }
        }
    }

    public function registerScriptStyles() {

        foreach( ($this->config['styles']??[]) as $build => $module ) {
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
        foreach( ($this->config['scripts']??[]) as $build => $module ) {
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


}