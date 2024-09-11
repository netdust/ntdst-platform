<?php
/**
 * A facade to make the Application instance globally available.
 *
 * @package Netdust
 */

namespace Netdust;


use lucatume\DI52\Container;
use Netdust\Logger\Logger;

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
     * Returns the singleton instance of the application
     *
     * @return ApplicationProvider
     */
    public static function application(): ?ApplicationProvider {
        return static::$app;
    }

    /**
     * Sets the application instance.
     *
     * @param ApplicationProvider $app
     *
     * @return void The method does not return any value.
     */
    public static function setApplication(ApplicationProvider $app): void {
        static::$app = $app;
        static::container()->register( $app->name );
    }

    /**
     * container Getter.
     *
     * @since 1.0.0
     *
     * @return Container
     */
    public static function container(): Container {
        return static::$app->container();
    }

    /**
     * binding the Application as a Singleton and starting the initialisation
     */
    public static function boot( string $id, array $args ): void {

        $args = array_merge(['name'=>$id], $args);
        $container = new \lucatume\DI52\Container();

        $container->singleton($id, new ApplicationProvider($container, $args) );
        static::setApplication( $container->get( $id ) );

    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed The entry for an id.
     *
     */
    public static function get( string $id = '' ): mixed {
        if( empty( $id ) ) return static::$app;
        else return static::$app->container()->get( $id );
    }

    /**
     * creates a singleton instance and returns it.
     *
     * @return mixed The instance for an id.
     *
     */
    public static function make( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null ): mixed
    {
        return static::$app->make( $id, $implementation, $args, $afterBuildMethods );
    }

}
