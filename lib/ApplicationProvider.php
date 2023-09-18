<?php

namespace Netdust;

use lucatume\DI52\Container;
use lucatume\DI52\ServiceProvider;
use Netdust\Traits\Templates;
use Netdust\Utils\Logger\LoggerInterface;

use Netdust\Traits\Setters;

class ApplicationProvider extends ServiceProvider {
    use Setters;
    use Templates;

    public $name = 'Netdust';

    public $text_domain = 'netdust';

    public $version = '1.2.0';

    public $minimum_php_version = '7.6';

    public $minimum_wp_version = '6.0';

    public $build_path = "/app";

    protected $config = null;

    protected $file;

    /**
     * URL Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function url() {
        return untrailingslashit( plugins_url( '/', $this->file ) );
    }

    /**
     * CSS URL Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function css_url() {
        return apply_filters( 'ntdst_css_url', $this->url() . $this->build_path .'/assets/css' );
    }

    /**
     * JS URL Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function js_url() {
        return apply_filters( 'ntdst_js_url', $this->url() . $this->build_path .'/assets/js' );
    }

    /**
     * Directory Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function dir() {
        return untrailingslashit( plugin_dir_path( $this->file ) );
    }

    /**
     * Template Directory Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function tpl_dir() {
        return apply_filters( 'ntdst_template_path', $this->dir() . DIRECTORY_SEPARATOR . $this->build_path . '/templates' );
    }

    /**
     * __FILE__ Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function file() {
        return $this->file;
    }

    /**
     * version Getter.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function version() {
        return $this->version;
    }

    /**
     * container Getter.
     *
     * @since 1.0.0
     *
     * @return Container
     */
    public function container() {
        return $this->container;
    }

    /**
     * Internationalization
     *
     * @since 2.1
     */
    public function i18n( $text ) {
        return __( $text, $this->text_domain );
    }

    public function __construct(Container $container, $args = [] ) {
        $this->set_values( $args );
        parent::__construct( $container );
    }

    public function boot( ) {

    }

    public function register( ) {

        // First, check to make sure the minimum requirements are met.
        if ( $this->_plugin_is_supported() ) {

            $this->_load_config_if_exists();

            $this->_register_if_exists();

            $this->_dependencies( );

            //add_action( 'after_setup_theme', function() {
                $this->container->boot();
                $this->get( LoggerInterface::class )->info(
                    'The application ' . $this->name . ' has been loaded.',
                    'application_load'
                );
            //});

        } else {
            // Run unsupported actions if requirements are not met.
            $this->_unsupported_actions();
        }

    }

    public function get( $id ) {
        return $this->container->get( $id );
    }

    protected function _dependencies() {

        /**
         * add wordPress dependencies, this helps creating a unified api
         * Bind a dependency class to a Registry, we can access it easly
         * The Registry is told to make dependency classes as singleton
         * note : passing parameter $key as array prevents the object from being build
         * **/
        $dependencies = $this->config( 'dependencies' );
        if( is_array( $dependencies) && count( $dependencies) > 0 ) {
            array_walk($dependencies, function(&$value, $key)  {
                $this->container->when( $key )->needs('$instanceClass')->give( [$key] );
                $this->container->singleton($key, $value[0]);
                if( $this->container->get($key) instanceof ServiceProvider ) {
                    $this->container->get($key)->register();
                }
            });
        }

        /**
         * register the other serviceproviders, this is early in the process
         * we can still register actions in the serviceproviders
         */
        $providers = $this->config( 'providers' );
        if( is_array( $providers ) && count( $providers ) > 0 ) {
            foreach($providers as $key => $value ) {
                if( is_array($value) ) // map alias too
                    call_user_func_array( [$this->container,'register'], $value );
                else {
                    $this->container->register( $value );
                }

            };
        }
    }

    /**
     * get a config value.
     *
     * @param string      $mod module where to search in.
     * @param string $key key to search for.
     */
    public function config($mod, $key='') {
        if( empty( $mod ) || !isset( $this->config[$mod] ) )
            return null;

        if( $key=='' )
            return $this->config[$mod];

        return isset( $this->config[$mod][$key] ) ?? null;
    }


    protected function _plugin_is_supported() {
        global $wp_version;
        $supports_php_version = version_compare( phpversion(), $this->minimum_php_version, '>=' );
        $supports_wp_version = version_compare( $wp_version, $this->minimum_wp_version, '>=' );
        return $supports_php_version && $supports_wp_version;
    }

    protected function _unsupported_actions() {
        global $wp_version;

        self::$instances[ __CLASS__ ] = new \WP_Error(
            'minimum_version_not_met',
            __( sprintf(
                "The plugin requires at least WordPress %s, and PHP %s.",
                $this->minimum_wp_version,
                $this->minimum_php_version
            ), $this->text_domain ),
            array( 'current_wp_version' => $wp_version, 'php_version' => phpversion() )
        );

        add_action( 'admin_notices', array( $this, 'below_version_notice' ) );
    }

    protected function _register_if_exists() {
        $path = $this->dir() . '/register/';

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                call_user_func(function ($bootstrap) {
                    $bootstrap($this);
                }, require_once($file));
            }
        }
    }

    protected function _load_config_if_exists() {

        $data = [];
        $path = $this->dir() . $this->build_path . '/config/';

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                $data[basename($file, '.php')] = require_once($file);
            }
        }

        $this->config = $data;

    }

    protected function get_template_group()
    {
        return '';
    }

    public function __call( $method, $arguments ) {
        // If this method exists, bail and just get the method.
        if ( method_exists( $this, $method ) ) {
            return $this->$method( ...$arguments );
        }

        return new \WP_Error(
            'method_not_found',
            "The method could not be called. Either register this item as dependecy, install an extension, or create a method for this call.",
            [
                'method'    => $method,
                'args'      => $arguments,
                'backtrace' => debug_backtrace(),
            ]
        );
    }


}