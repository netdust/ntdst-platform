<?php
/**
 * A facade to make the Application instance globally available.
 *
 * @package Netdust
 */

namespace Netdust;


use lucatume\DI52\Container;

/**
 * Class App
 *
 */
class App
{
    /** A reference to the singleton instance of the application.
     *
     * @var ApplicationProvider|null
     */
    protected static $app;

    /**
     * Returns the singleton instance of the application
     *
     * @return ApplicationProvider
     */
    public static function application()
    {
        return static::$app;
    }

    /**
     * Sets the application instance.
     *
     * @param ApplicationProvider $app
     *
     * @return void The method does not return any value.
     */
    public static function setApplication(ApplicationProvider $app)
    {
        static::$app = $app;
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
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed The entry for an id.
     *
     */
    public static function get( $id ) {
        return static::$app->container()->get( $id );
    }
}
