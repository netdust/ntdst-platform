<?php

namespace Netdust;

use lucatume\DI52\Container;
use lucatume\DI52\ServiceProvider;
use Netdust\Core\Config;
use Netdust\Core\Factory;
use Netdust\Core\File;
use Netdust\Core\Requirements;
use Netdust\Http\Request;
use Netdust\Http\Response;
use Netdust\Http\Router\RouterInterface;
use Netdust\Traits\Mixins;
use Netdust\Traits\Setters;

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


    /**
     * Config Getter.
     */
    public function config(): Config {
        return $this->container->get( Config::class );
    }

    /**
     * Request Getter.
     */
    public function request(): Request {
        return $this->container->get( Request::class );
    }

    /**
     * File Getter.
     */
    public function file():File {
        return $this->container->get( File::class );
    }

    /**
     * container Getter.
     */
    public function container(): Container {
        return $this->container;
    }

    /**
     * name Getter
     */
    public function name(): string {
        return $this->name;
    }

    /**
     * text_domain Getter.
     */
    public function textdomain(): string {
        return $this->text_domain;
    }


    /**
     * version Getter.
     */
    public function version(): string {
        return $this->version;
    }


    public function __construct(Container $container, array $args = [] ) {
        $this->set_values( $args );
        parent::__construct( $container );
    }

    public function register( ): void {

        //make sure we use 1 instance
        $this->container->singleton( Request::class );
        $this->container->singleton( Response::class );
        $this->container->singleton( Factory::class );

        // add path builder to application
        $this->container->singleton( File::class, new File( $this->file, trim($this->build_path,'/') ) );

        // Make application accessible using its container
        if( !$this->container->has( ApplicationInterface::class ) ){
            $this->container->singleton(ApplicationInterface::class, $this );
        }

        // Check if environment meets requirements
        $requirements = new Requirements( $this, array(
            'php'         => $this->minimum_php_version,
            'wp'          => $this->minimum_wp_version,
            'plugins'     => $this->required_plugins,
            'theme'       => $this->required_theme,
        ) );

        // First, check to make sure the minimum requirements are met.
        if ( $requirements->satisfied( ) ) {

            add_action( 'plugins_loaded', function(){

                $this->container->singleton( Config::class, new Config(
                    $this->file()->dir_path( $this->config_path )
                ) );

                $this->container->register( \Netdust\Logger\LoggerService::class );
                $this->container->register( \Netdust\View\TemplateServiceProvider::class );
                $this->container->register( \Netdust\Http\Router\WPRouterService::class );

                do_action('application/register', $this );

                $this->_register_if_exists();

                $this->container->boot();

                do_action('application/init', $this );

            });

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

    protected function _register_if_exists( ?string $path = null ): void {
        $path = $path ?? $this->file()->plugin_path() . '/register/';

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                call_user_func(function ($bootstrap) {
                    $bootstrap($this);
                }, require_once($file));
            }
        }

    }

    public function __call( $method, $parameters ): mixed {
        
        // check because App calls this directly and bypasses normal behaviour
        if ( method_exists( $this, $method ) ) {
            if ($method === '__call') {
                return new \WP_Error('invalid_call', 'Cannot call __call recursively.');
            }
            return $this->$method(...$parameters);
        }

        // Check if the container has an instance bound with this name
        if( $this->container->has( $method ) && count($parameters)==0 ){
            return $this->container->get( $method );
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