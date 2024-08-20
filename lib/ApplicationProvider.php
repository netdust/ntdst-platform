<?php

namespace Netdust;

use lucatume\DI52\Container;
use lucatume\DI52\ServiceProvider;
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
    public string $config_path = "";
    public string $build_path = "app";

    protected ?array $config = null;

    protected string $file;

    protected function trim_path( string $path='' ): string {
        return $path ? DIRECTORY_SEPARATOR . trim($path,'/') . DIRECTORY_SEPARATOR : $path;
    }
    public function url( string $path='' ): string {
        return untrailingslashit( plugins_url( '/', $this->file ) ) . $this->trim_path($path);
    }

    public function css_url() {
        return apply_filters( 'ntdst_css_url', $this->url(  $this->build_path .'/assets/css' ) );
    }

    public function js_url() {
        return apply_filters( 'ntdst_js_url', $this->url( $this->build_path .'/assets/js' ) );
    }

    public function dir( string $path='' ): string {
        return untrailingslashit( plugin_dir_path( $this->file ) ) .  $this->trim_path($path);
    }

    public function app_dir( string $path='' ): string {
        return $this->dir( $this->build_path ) .  $this->trim_path($path);
    }

    public function template_dir( string $path = ''): string {
        return $this->app_dir( 'templates' ) .  $this->trim_path($path);
    }

    public function content_dir( string $path = ''): string {
        return get_home_path() .  $this->trim_path($path);
    }

    public function plugins_dir( string $path = ''): string {
        return $this->content_dir('plugins') . $this->trim_path($path);
    }

    public function themes_dir( string $path = ''): string {
        return $this->content_dir('themes') .  $this->trim_path($path);
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

            //add_action('wp_loaded', function(){
                $this->_register_if_exists();

                $this->container->boot();

                $this->make( LoggerInterface::class )->info(
                    'The application ' . $this->name . ' has been loaded.',
                    'application_load'
                );
           //});

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
     * @param string $mod module where to search in.
     * @param string $key key to search for.
     */
    public function config( string $mod, string $key=''): mixed {
        if( empty( $mod ) ) return null;

        if( !isset( $this->config[$mod] ) ) {
            $this->_load_config_if_exists( $mod );
            if( !isset( $this->config[$mod] ) ) {
                return null;
            }
        }

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
        $path = $path ?? $this->dir( $this->config_path );

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                $kkey = (!empty($key)?$key:basename($file, '.php'));
                $data[$kkey] = array_merge( $data[$kkey]??[], require_once($file) );
            }
        }

        $this->config = array_merge( $this->config??[], $data );

    }



}