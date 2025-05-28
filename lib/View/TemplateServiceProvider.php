<?php

namespace Netdust\View;


use LogicException;
use lucatume\DI52\ServiceProvider;
use Netdust\ApplicationInterface;
use Netdust\Core\File;
use Netdust\Logger\Logger;
use Netdust\Traits\Mixins;
use Netdust\View\UI\UI;
use Netdust\View\UI\UIHelper;
use Netdust\View\UI\UIInterface;


class TemplateServiceProvider extends ServiceProvider {

    public function register( ): void {

        $this->container->singleton(
            TemplateInterface::class,
            new \Netdust\View\Template( [
                $this->container->get( File::class )->dir_path(),
                $this->container->get( File::class )->template_path(),
                $this->container->get( File::class )->dir_path('services')
                ]
            )
        );

        $this->template_mixin( $this->container->get( ApplicationInterface::class ) );

    }

    public function make( string|array $template_root = '', array $globals = [] ): Template {
        return new \Netdust\View\Template( $template_root, $globals );
    }

    public function add( string $layout, array $data = [] ): void {
        $this->container->get( TemplateInterface::class )->add( $layout, $data );
    }
    public function render( string $layout, array $data = [] ): string {
        return $this->container->get( TemplateInterface::class )->render( $layout, $data );
    }

    public function print( string $layout, array $data = [] ): void {
        $this->container->get( TemplateInterface::class )->print( $layout, $data );
    }

    public function template_mixin( ServiceProvider $service, ?TemplateInterface $template = null ): void {

        if ( !in_array(Mixins::class, class_uses($service), true) ) {
            throw new LogicException('The ServiceProvider is not using the mixins Trait.');
        }

        if( $template == null ) {
            $template = $this->container->get( TemplateInterface::class );
        }

        $service->mixin( 'render', function(string $layout, array $data = array() ) use ( $template ): string {
            return $template->render( $layout, $data );
        });
        $service->mixin( 'print', function(string $layout, array $data = array() ) use ( $template ): void {
            $template->print( $layout, $data );
        });
        $service->mixin( 'exists', function(string $layout ) use ( $template ): bool {
            return $template->exists( $layout );
        });
        $service->mixin( 'template', function() use ( $template ): Template {
            return $template;
        });
    }
}