<?php

namespace Netdust\Http\Router;

use AltoRouter;
use Netdust\Http\Request;
use Netdust\Http\URL;

class SimpleRouter implements RouterInterface {

    protected $router;

    /**
     * @var array Array of all routes (incl. named routes).
     */
    protected $routes = [];

    /**
     * @var array Array of all named routes.
     */
    protected $namedRoutes = [];

    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    protected $basePath = '';

    public function __construct(  ) {
        $siteUrlParts = explode('/', URL::removeTrailingSlash( get_bloginfo('url') ));
        $basePath = implode('/', array_slice($siteUrlParts, 3));
        $this->setBasePath( URL::addLeadingTrailingSlash( $basePath ) );

        add_action('wp_loaded', [$this, 'processRequest']);
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Attempt to match the current request against the defined routes
     *
     * If a route matches the Response will be sent to the client and PHP will exit.
     *
     */
    public function processRequest()
    {
        $response = $this->match(
            \Netdust\App::get( Request::class )->getPath(),
            \Netdust\App::get( Request::class )->getMethod() );

        if ( $response ) {
            echo $response;
            exit();
        }
    }

    private function initRouter() {
        if( !isset($this->router) ) {
            $this->router = new AltoRouter();

            $this->router->setBasePath($this->basePath);

            foreach ($this->routes as list( $method, $uri, $callback ) ) {
                $name = in_array( $uri, $this->namedRoutes) ? array_search($uri, $this->namedRoutes) : null;
                $this->router->map(  $method, ltrim( $uri, '/'), $callback, $name );
            }
        }
    }

    public function match(string $requestUri, string $method)  {

        $this->initRouter();

        $route = $this->router->match( '/' . ltrim( $requestUri, '/'), $method);

        if( $route !== FALSE ) {
            foreach ( $route['params'] as $param => $value ) {
                \Netdust\App::get( Request::class )->set_var( $param, $value );
            }
            return call_user_func_array( $route['target'], $route['params'] );
        }

        return FALSE;
    }

    public function generate( $routeName, array $params = [] ): string {

        $this->initRouter();

        return $this->router->generate( $routeName, $params );
    }

    /**
     * Retrieves all routes.
     * Useful if you want to process or display routes.
     * @return array All routes.
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function addRoute(array $route)
    {
        $this->routes[] = $route;
    }

    public function map(array $methods, string $uri, $callback, $name)
    {
        $this->addRoute( [implode("|", $methods), $uri, $callback] );

        if ($name) {
            $this->namedRoutes[$name] = $uri;
        }
    }
}