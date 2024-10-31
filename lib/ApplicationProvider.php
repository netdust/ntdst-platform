<?php

namespace Netdust;

use lucatume\DI52\Container;
use lucatume\DI52\ServiceProvider;

use Netdust\Core\File;
use Netdust\Core\Config;
use Netdust\Core\Requirements;
use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use Netdust\Traits\Collection;
use Netdust\Traits\Mixins;
use Netdust\Traits\Setters;
use ReflectionClass;

interface APIInterface {}

class ApplicationProvider extends ServiceProvider implements ApplicationInterface {

    use Setters;
    use Mixins;

    public string $name = 'Netdust';
    public string $text_domain = 'netdust';
    public string $version = '1.2.0';

    /**
     * requirements for this plugin
     */
    protected string $minimum_php_version = '7.6';
    protected string $minimum_wp_version = '6.0';
    protected array $required_plugins = [];
    protected string $required_theme = '';

    /**
     * paths
     */
    protected string $config_path = "app/config";
    protected string $build_path = "app";
    protected string $file;

    protected ?Config $config = null;

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
     * File Getter.
     *
     * @since 3.0.0
     *
     * @return File
     */
    public function file():File {
        return $this->container->get( File::class );
    }


    /**
     * name Getter.
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function name(): string {
        return $this->name;
    }

    /**
     * text_domain Getter.
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function textdomain(): string {
        return $this->text_domain;
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

    public function register( ): void {

        // Make application accessible using its container
        if( !$this->container->has( ApplicationInterface::class ) ){
            $this->container->singleton(ApplicationInterface::class, function( Container $container ) {
                return $this;
            });
        }

        // Check if environment meets requirements
        $this->container->singleton( Requirements::class, new Requirements( $this, array(
            'php'         => $this->minimum_php_version,
            'wp'          => $this->minimum_wp_version,
            'plugins'     => $this->required_plugins,
            'theme'       => $this->required_theme,
        ) ) );
        $requirements = $this->container->get( Requirements::class );

        // add path builder to application
        $this->container->singleton( File::class, new File( $this->file ) );
        $file = $this->container->get( File::class );

        // First, check to make sure the minimum requirements are met.
        if ( $requirements->satisfied( ) ) {

            $this->container->singleton( Config::class, new Config(
                $file->dir_path( $this->config_path )
            ) );
            $this->config = $this->container->get( Config::class );

            $this->_register_if_exists();

            $this->container->boot();

            $this->make( LoggerInterface::class )->info(
                'The application ' . $this->name . ' has been loaded.',
                'application_load'
            );

            do_action('application/init');

        } else {
            // Run unsupported actions if requirements are not met.
            $requirements->print_notice();
        }


    }

    public function boot( ): void {

    }

    public function get( string $id = '' ): mixed {
        if( empty( $id ) ) return $this;
        else return $this->container->get( $id );
    }


    public function make( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null ): mixed {

        if(!empty($args) )  {

            $className = !empty($implementation) ? $implementation:$id;

            if( class_exists($className) ) {
                $constructor = ( new ReflectionClass($className) )->getConstructor();
                $parameters = $constructor ? $constructor->getParameters() : [];

                foreach ($parameters as $parameter) {
                    if( $parameter->getName() == 'args' ) {
                        $this->container->when( $id )->needs('$args' )->give( $args );
                    }
                    else if( key_exists($parameter->getName(),$args) ) {
                        $this->container->when( $id )->needs('$'.$parameter->getName() )->give( $args[$parameter->getName()] );
                    }
                }
            }

        }

        if(!empty($implementation) ) {

            if( !empty($args) && key_exists('middlewares', $args ) ) {
                $args['middlewares'][] = $implementation;
                $this->container->bindDecorators($id, $args['middlewares'], $afterBuildMethods, true );
            }
            else {
                $this->container->bind( $id, $implementation, $afterBuildMethods );
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
    public function config( string $mod='', string $key=''): mixed {

        if( $mod=='' )
            return $this->config;

        if( $key=='' )
            return $this->config[$mod];

        return isset( $this->config[$mod][$key] ) ?? null;

    }

    /**
     * add a config value.
     *
     * @param string $mod module where to search in.
     * @param string $key key to search for.
     */
    public function add_config( string $mod, string|array $configuration ): void {

        if( !is_array( $configuration ) ) {
            $this->config->add( $mod, $this->config->load($configuration) );
        }
        else {
            $this->config->add( $mod, $configuration );
        }

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

    public function __call( $method, $parameters ): mixed {

        if ( method_exists( app( APIInterface::class ), $method ) ) {
            return app( APIInterface::class )->$method( ...$parameters );
        }

        // we need to check this, otherwise the trait Mixins will be skipped
        if( $this->hasMixin( $method ) ){
            return $this->callMixin($method, ...$parameters);
        }

        return new \WP_Error(
            'method_not_found',
            "The method could not be called. Either register this method as api, or create a method for this call.",
            [
                'method'    => $method,
                'args'      => $parameters
            ]
        );
    }

}