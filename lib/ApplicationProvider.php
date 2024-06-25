<?php

namespace Netdust;

use lucatume\DI52\Container;
use lucatume\DI52\ServiceProvider;
use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use Netdust\Traits\Mixins;
use Netdust\Traits\Setters;

interface APIInterface {}

class ApplicationProvider extends ServiceProvider {
    use Setters;
    use Mixins;

    public string $name = 'Netdust';
    public string $text_domain = 'netdust';

    public string $version = '1.2.0';
    public string $minimum_php_version = '7.6';
    public string $minimum_wp_version = '6.0';
    public string $build_path = "app";

    protected ?array $config = null;

    protected string $file;

    public function url( string $path='' ): string {
        return untrailingslashit( plugins_url( '/', $this->file ) ) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function css_url() {
        return apply_filters( 'ntdst_css_url', $this->url(  $this->build_path .'/assets/css' ) );
    }

    public function js_url() {
        return apply_filters( 'ntdst_js_url', $this->url( $this->build_path .'/assets/js' ) );
    }

    public function dir( string $path='' ): string {
        return untrailingslashit( plugin_dir_path( $this->file ) ) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function app_dir( string $path='' ): string {
        return $this->dir( $this->build_path ) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function template_dir( string $path = ''): string {
        return $this->app_dir( 'templates' ) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function content_dir( string $path = ''): string {
        return WP_CONTENT_DIR . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function plugins_dir( string $path = ''): string {
        return $this->content_dir('plugins') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function themes_dir( string $path = ''): string {
        return $this->content_dir('themes') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }


    /**
     * __FILE__ Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function file(): string {
        return $this->file;
    }

    /**
     * version Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function version(): string {
        return $this->version;
    }

    /**
     * container Getter.
     *
     * @since 1.0.0
     *
     * @return Container
     */
    public function container(): Container {
        return $this->container;
    }

    public function __construct(Container $container, array $args = [] ) {
        $this->set_values( $args );
        parent::__construct( $container );
    }

    public function boot( ): void {

    }

    public function register( ): void {

        // First, check to make sure the minimum requirements are met.
        if ( $this->_plugin_is_supported() ) {

            $this->_load_config_if_exists();

            $this->_register_if_exists();

            $this->container->boot();

            $this->make( LoggerInterface::class )->info(
                'The application ' . $this->name . ' has been loaded.',
                'application_load'
            );

        } else {
            // Run unsupported actions if requirements are not met.
            $this->_unsupported_actions();
        }

    }

    public function get( string $id ): mixed {
        return $this->container->get( $id );
    }

    public function make( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null ): mixed {
        if(!empty($args) )
            $this->container->when( $id )->needs('$args' )->give( $args );

        if(!empty($implementation) ) {

            if( !empty($args) && key_exists('middlewares', $args ) ) {
                $args['middlewares'][] = $implementation;
                $this->container->singletonDecorators($id, $args['middlewares'], $afterBuildMethods, true );
            }
            else {
                $this->container->singleton( $id, $implementation, $afterBuildMethods );
            }
        }

        return $this->container->get( $id );
    }


    /**
     * get a config value.
     *
     * @param string      $mod module where to search in.
     * @param string $key key to search for.
     */
    public function config( string $mod, string $key=''): mixed {
        if( empty( $mod ) || !isset( $this->config[$mod] ) )
            return null;

        if( $key=='' )
            return $this->config[$mod];

        return isset( $this->config[$mod][$key] ) ?? null;
    }

    public function load_config( string $key, string $path ){
        $this->_load_config_if_exists( $key, $path );
    }


    protected function _plugin_is_supported(): bool {
        global $wp_version;
        $supports_php_version = version_compare( phpversion(), $this->minimum_php_version, '>=' );
        $supports_wp_version = version_compare( $wp_version, $this->minimum_wp_version, '>=' );
        return $supports_php_version && $supports_wp_version;
    }

    protected function _unsupported_actions(): void {

        add_action( 'admin_notices', function() {
            $class = 'notice notice-error';
            $message = __( sprintf(
                "The plugin requires at least WordPress %s, and PHP %s.",
                $this->minimum_wp_version,
                $this->minimum_php_version
            ), $this->text_domain );

            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        } );

    }

    protected function _register_if_exists( ?string $path = null ): void {
        $path = $path ?? $this->dir() . '/register/';

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                call_user_func(function ($bootstrap) {
                    $bootstrap($this);
                }, require_once($file));
            }
        }
    }

    protected function _load_config_if_exists( string $key='', ?string $path = null ): void {

        $data = [];
        $path = $path ?? $this->dir() . $this->build_path . '/config/';

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                $kkey = (!empty($key)?$key:basename($file, '.php'));
                $data[$kkey] = array_merge( $data[$kkey]??[], require_once($file) );
            }
        }

        $this->config = array_merge( $this->config??[], $data );

    }


    /*
    public function __call( $method, $arguments ): mixed {
        // If this method exists, bail and just get the method.
        if ( method_exists( $this, $method ) ) {
            return $this->$method( ...$arguments );
        }


        if ( $this->container->has( $method ) && is_callable($this->container->get( $method )) ) {
            return $this->container->get( $method )( ...$arguments );
        }

        if ( $this->container->has( APIInterface::class ) && method_exists( $this->container->get( APIInterface::class ), $method ) ) {
            return $this->container->get( APIInterface::class )->$method( ...$arguments );
        }

        return new \WP_Error(
            'method_not_found',
            "The method could not be called. Either register this item as dependency, install an extension, or create a method for this call.",
            [
                'method'    => $method,
                'args'      => $arguments,
                'backtrace' => debug_backtrace(),
            ]
        );
    }*/


}