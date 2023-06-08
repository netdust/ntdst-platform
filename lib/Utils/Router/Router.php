<?php

namespace Netdust\Utils\Router;

use Netdust\Core\Request;
use Netdust\Utils\Logger\LoggerInterface;
use Netdust\Utils\Router\RouterInterface;
use Netdust\Utils\Router\SimpleRouter;
use Netdust\Utils\Logger\SimpleLogger;


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
            static::setRouter( new SimpleRouter() );
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
        return static::$router->match($request->getPath(), $request->getMethod());
    }



    /**
     * Map a route
     *
     * @param array $verbs
     * @param string $uri
     * @param callable|string $callback
     * @param string $name
     */
    public static function map(array $verbs, string $uri, $callback, string $name = null)
    {
        return static::$router->map($verbs, $uri, $callback, $name);
    }


    /**
     * Get the URL for a named route
     *
     * @param string $name
     * @param array $params
     * @return string
     * @throws Exception
     */
    public static function generate(string $name, $params = []): string
    {
        return static::$router->generate($name, $params);
    }

    public static function virtual(string $uri, $virtualPage, $callback=null)
    {
        $virtualPage->setUri($uri);
        static::map(['GET', 'POST'], $uri, [$virtualPage, $callback??'onRoute'], $virtualPage->template() );
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