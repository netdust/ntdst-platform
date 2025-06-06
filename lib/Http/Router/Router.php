<?php

namespace Netdust\Http\Router;

use Netdust\Http\Request;
use Netdust\Http\Response;
use Netdust\Logger\Logger;


class Router
{
    /** A reference to the singleton instance of the RouterInterface
     * the application uses as log service.
     *
     * @var RouterInterface|null
     */
    protected static  $router;

    /**
     * Returns the singleton instance of the RouterInterface the application
     * will use as a log service.
     *
     * @return RouterInterface The singleton instance of the RouterInterface
     */
    public static function router( )
    {
        if (!isset(static::$router)) {
            self::setRouter(  new WPRouter( ) );
        }

        return static::$router;
    }

    /**
     * Sets the router instance the Application should use as a router
     *
     * @param RouterInterface $router A reference to the Router instance the Application
     *                             should use as a router Locator.
     *
     * @return void The method does not return any value.
     */
    public static function setRouter(RouterInterface $router)
    {
        static::$router = $router;
    }


    /**
     * Match the provided Request against the defined routes and return a Response
     *
     * @param Request $request
     * @return Response
     */
    public static function match(Request $request = null): Response
    {
        return self::router()->match($request->getPath(), $request->getMethod());
    }


    /**
     * Map a route
     *
     * @param array $verbs
     * @param string $uri
     * @param callable|string $callback
     */
    public static function map(array $verbs, string $uri, $callback)
    {
        return self::router()->map($verbs, $uri, $callback);
    }


    public static function group(string $prefix, $callback): RouterInterface
    {
        return self::router()->group($prefix, $callback);
    }

    /**
     * Get the URL for a named route
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public static function generate(string $name, $params = []): string
    {
        return self::router()->generate($name, $params);
    }

    public static function virtual(string $uri, $virtualPage, $callback=null)
    {
        return self::router()->virtual($uri, $virtualPage , $callback);
    }

    /**
     * Shutdown PHP
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected static function shutdown()
    {
        exit();
    }
}