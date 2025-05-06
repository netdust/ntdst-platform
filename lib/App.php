<?php
/**
 * A facade to make the Application instance globally available.
 *
 * @package Netdust
 */

namespace Netdust;


use lucatume\DI52\Container;
use Netdust\Core\Config;
use Netdust\Core\File;
use Netdust\Http\Router\RouterInterface;
use Netdust\Logger\Logger;
use Netdust\View\TemplateInterface;

/**
 * Class App
 *
 */
class App
{
    /** A reference to the singleton instance of the application.
     */
    protected static ApplicationProvider|null $app;

    /** The name/id of the application.
     */
    public static string $name;

    /**
     * binding the Application as a Singleton and starting the initialisation
     */
    public static function boot( string $id, array $args ): void {

        $container = new \lucatume\DI52\Container();

        $container->singleton(
            $id,
            new ApplicationProvider($container, array_merge(['name'=>$id], $args))
        );
        static::setApplication( $container->get( $id ) );
        $container->register( $id, ApplicationInterface::class );

    }

    /**
     * Returns the singleton instance of the application
     *
     * @return ApplicationProvider
     */
    public static function application(): ?ApplicationProvider
    {
        return static::$app ?? null;
    }

    /**
     * Sets the application instance.
     *
     * @param ApplicationProvider $app
     *
     * @return void The method does not return any value.
     */
    public static function setApplication(ApplicationProvider $app): void
    {
        static::$app = $app;
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        if( method_exists( static::$app, $name ) && count( $arguments ) == 0 ) {
            return static::application()->$name();
        }

        if( static::application()->container()->has( $name ) && count($arguments)==0 ){
            return static::application()->container()->get( $name );
        }

        if( count( $arguments ) == 0 && static::$app->container()->has( $name ) ) {
            return static::$app->container()->get( $name );
        }

        return new \WP_Error(
            'method_not_found',
            "The method could not be called. Either register this method as api, or create a method for this call.",
            [
                'method'    => $name,
                'args'      => $arguments
            ]
        );
    }


    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed The entry for an id.
     *
     */
    public static function get( string $id = '' ): mixed
    {
        return static::$app->get( $id );
    }

    /**
     * creates a singleton instance and returns it.
     *
     * @return mixed The instance for an id.
     *
     */
    public static function make( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null, bool $singleton = false  ): mixed
    {
        return static::$app->make( $id, $implementation, $args, $afterBuildMethods, $singleton );
    }

}
