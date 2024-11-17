<?php

namespace Netdust\Http\Router;

use AltoRouter;
use Netdust\Http\Request;
use Netdust\Http\Response;
use Netdust\Logger\Logger;

/**
 * Route collector for WordPress REST routes
 */
class WPRouterService implements RouterInterface
{

    /**
     * @var AltoRouter actual router.
     */
    protected AltoRouter $router;


    /**
     * @var array Array of all routes (incl. named routes).
     */
    protected array $routes = [];


    public function __construct(  ) {

        $this->boot( );

    }

    public function boot(Request $request = null)
    {
        if ( !empty( $this->router ) ) {
            return;
        }

        if (did_action('parse_request')) {
            throw new \BadMethodCallException(
                sprintf('%s must be called before "do_parse_request".', __METHOD__)
            );
        }

        add_filter('do_parse_request', function ($do, \WP $wp) use ($request) {
            return $this->parse_request( $request ?? app()->get( Request::class ), $wp );
        }, 100, 2);

    }

    public function parse_request( $request, \WP $wp ): bool  {

        $response = $this->match( $request->getUri(), $request->getMethod() );

        // send out response, if virtual page
        if ($response && !$response->isForbidden()) {
            //$wp->init();
            //$wp->query_posts();
            //$wp->register_globals();
            $response->send();
            return false;
        }

        return true;
    }

    public function group($prefix, $callback): RouterInterface
    {
        $group = new WPRouterGroup($prefix, $this);

        call_user_func($callback, $group);

        return $this;
    }

    public function virtual(string $uri, $virtualPage, $callback=null) : WPRoute
    {
        return $this->map( ['GET','POST'], $uri, [$virtualPage, $callback??'onRoute']);
    }

    public function map(array $methods, string $uri, $callback): WPRoute {

        // Force all verbs to be uppercase
        $verbs = array_map('strtoupper', $methods);

        $route = new WPRoute($verbs, $uri, $callback);

        $this->routes[] = $route;

        return $route;

    }

    public function match(string $requestUri, string $method): mixed {

        $this->create_router();

        $route = $this->router->match( '/' . ltrim( $requestUri, '/'), $method);

        if( $route !== FALSE ) {
            return call_user_func_array( $route['target'], $route['params'] );
        }

        return null;
    }

    /**
     * Check if route exists
     */
    public function has(string $name): bool
    {
        return !empty($this->get($name));
    }

    /**
     * Get named route
     */
    public function get(string $name): WPRoute
    {
        $route = array_values(array_filter($this->routes, function ($route) use ($name) {
            return $route->getName() === $name;
        }));

        return array_shift($route);
    }

    /**
     * Create url of the route, add nonce if needed
     */
    public function generate( string $routeName, array $params = [] ): string {

        $this->create_router();

        $route = $this->get( $routeName );
        $route_url = $this->router->generate( $routeName, $params );

        if( !empty( $nonce = $route->getNonce() )) {
            $route_url = add_query_arg( [ $nonce['nonce_name'] => wp_create_nonce( $nonce['nonce_action'] ) ], $route_url );
        }

        return $route_url;
    }


    private function create_router(): void {
        if( !isset($this->router) ) {

            $this->router = new AltoRouter();
            $this->router->setBasePath($this->getPath());

            foreach ($this->routes as $route) {

                $methods = implode('|', $route->getMethods());
                $uri = trim($route->getUri(), '/') .'/';

                $this->router->map( $methods, $uri , function ( ...$args ) use ( $route ): Response {
                        return $this->handleRoute($args, $route);
                    }, $route->getName() ?? null
                );
            }
        }
    }



    /**
     * Handles route execution with WordPress auth and nonce
     */
    private function handleRoute( array $args, WPRoute $route ): Response {


        $error = [];

        if( !empty( $nonce = $route->getNonce() ) ) {
            $nonce_name = sanitize_text_field( wp_unslash( $_GET[ $nonce['nonce_name'] ] ) );
            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                $valid = check_ajax_referer($nonce_name, $nonce['nonce_action']);
            }
            else
                $valid = wp_verify_nonce($nonce_name, $nonce['nonce_action'] );

            if ($valid === false) {
                $error = ['error'=>'forbidden'];
            }
        }

        if( !empty( $roles = $route->getRoles() ) ) {
            $has_role = false;
            $current_user = wp_get_current_user();
            foreach ($roles as $role) {
                if (in_array(strtolower($role), $current_user->roles, true)) {
                    $has_role = true;
                    break;
                }
            }
            if ($has_role === false) {
                $error = ['error'=>'not authorized'];
            }
        }

        if( !empty( $error ) ) {
             return app()->get( Response::class )->withJson( $error , 403 );
        }
        else {
            //$args['payload'] = $this->getJsonPayload();
            return app()->get( Response::class )->withBody( call_user_func_array($route->getAction(), $args)??'', 200 );
        }

    }

    private function getPath()
    {
        $parts = explode('/',trim( get_bloginfo('url'), '/'));
        $basePath = implode('/', array_slice($parts, 3));

        return $basePath? '/'.$basePath.'/':'/';

    }

    /**
     * Decodes JSON body if set. Returns empty array if not set
     *
     * @return array
     */
    private function getJsonPayload(): array {
        $body = isset($_REQUEST['payload']) ? $_REQUEST['payload'] : null;
        if (empty($body)) {
            $body = null;
        }
        $payload = json_decode($body, true);
        if ($payload === null) {
            $payload = json_decode(wp_unslash($body), true);
        }

        return !empty($payload) ? $payload : [];
    }
}