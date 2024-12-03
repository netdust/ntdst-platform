<?php

namespace Netdust\Http\Router;

use AltoRouter;
use Netdust\Http\Request;
use Netdust\Http\Response;
use Netdust\Logger\Logger;
use Netdust\Service\Pages\VirtualPage;

/**
 * Route collector for WordPress REST routes
 */
class WPRouter implements RouterInterface
{

    /**
     * @var AltoRouter The actual router used to manage routes.
     */
    protected AltoRouter $router;

    /**
     * @var array Array of all routes, including named routes.
     */
    protected array $routes = [];

    /**
     * Constructor for the WPRouterService class.
     * Initializes the router service by booting it.
     */
    public function __construct() {
        $this->boot();
    }

    /**
     * Boots the router service and hooks into WordPress's request lifecycle.
     *
     */
    public function boot(Request $request = null) {
        if (!empty($this->router)) {
            return; // Prevent reinitialization if the router is already set.
        }

        // Ensure this method is called before WordPress processes requests.
        if (did_action('parse_request')) {
            throw new \BadMethodCallException(
                sprintf('%s must be called before "do_parse_request".', __METHOD__)
            );
        }

        // Hook into the WordPress request parsing process.
        add_filter('do_parse_request', function ($do, \WP $wp) use ($request) {
            return $this->parse_request($request ?? app()->get(Request::class), $wp);
        }, 100, 2);
    }

    /**
     * Parses incoming requests and matches them against defined routes.
     */
    public function parse_request($request, \WP $wp): bool {
        $response = $this->match($request->getUri(), $request->getMethod());

        // If a matching route is found and allowed, send the response.
        if ($response && !$response->isForbidden()) {
            $response->send();
            return false; // Prevent further WordPress processing.
        }

        return true; // Allow WordPress to handle the request.
    }

    /**
     * Groups routes under a common prefix for organizational purposes.
     */
    public function group(string $prefix, callable $callback): RouterInterface {
        $group = new WPRouterGroup($prefix, $this);
        call_user_func($callback, $group);

        return $this;
    }

    /**
     * Defines a virtual route that maps to a virtual page in WordPress.
     */
    public function virtual(string $uri, VirtualPage $virtualPage, callable $callback = null): WPRoute {
        return $this->map(['GET', 'POST'], $uri, [$virtualPage, $callback ?? 'onRoute']);
    }

    /**
     * Maps HTTP methods and a URI to a callback or handler.
     */
    public function map(array $methods, string $uri, $callback): WPRoute {
        $verbs = array_map('strtoupper', $methods); // Normalize HTTP methods to uppercase.
        $route = new WPRoute($verbs, $uri, $callback);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Matches a request URI and method against defined routes.
     */
    public function match(string $requestUri, string $method): mixed {
        $this->create_router();

        $route = $this->router->match('/' . ltrim($requestUri, '/'), $method);


        if ($route !== false) {
            return call_user_func_array($route['target'], $route['params']);
        }

        return null;
    }

    /**
     * Checks if a named route exists.
     */
    public function has(string $name): bool {
        return !empty($this->get($name));
    }

    /**
     * Retrieves a named route by its name.
     */
    public function get(string $name): WPRoute {
        $route = array_values(array_filter($this->routes, function ($route) use ($name) {
            return $route->getName() === $name;
        }));

        return array_shift($route);
    }

    /**
     * Generates a URL for a named route, optionally including a nonce.
     */
    public function generate(string $routeName, array $params = []): string {
        $this->create_router();

        $route = $this->get($routeName);
        $route_url = $this->router->generate($routeName, $params);

        if (!empty($nonce = $route->getNonce())) {
            $route_url = add_query_arg([$nonce['nonce_name'] => wp_create_nonce($nonce['nonce_action'])], $route_url);
        }

        return $route_url;
    }

    /**
     * Initializes the router and registers all defined routes.
     */
    private function create_router(): void {
        if (!isset($this->router)) {
            $this->router = new AltoRouter();
            $this->router->setBasePath($this->getPath());

            foreach ($this->routes as $route) {
                $methods = implode('|', $route->getMethods());
                $uri = trim($route->getUri(), '/') . '/';

                $this->router->map($methods, $uri, function (...$args) use ($route): Response {
                    return $this->handleRoute($args, $route);
                }, $route->getName() ?? null);
            }
        }
    }

    /**
     * Handles route execution with WordPress authorization and nonce validation.
     */
    private function handleRoute(array $args, WPRoute $route): Response {
        $error = [];

        // Validate nonce if required.
        if (!empty($nonce = $route->getNonce())) {
            $nonce_name = sanitize_text_field(wp_unslash($_GET[$nonce['nonce_name']]));
            $valid = is_admin() && defined('DOING_AJAX') && DOING_AJAX
                ? check_ajax_referer($nonce_name, $nonce['nonce_action'])
                : wp_verify_nonce($nonce_name, $nonce['nonce_action']);

            if ($valid === false) {
                $error = ['error' => 'forbidden'];
            }
        }

        // Check user roles if required.
        if (!empty($roles = $route->getRoles())) {
            $current_user = wp_get_current_user();
            $has_role = !empty(array_intersect(array_map('strtolower', $roles), $current_user->roles));

            if (!$has_role) {
                $error = ['error' => 'not authorized'];
            }
        }

        // Return error response or execute the route action.
        if (!empty($error)) {
            return app()->get(Response::class)->withJson($error, 403);
        }

        return app()->get(Response::class)->withBody(
            call_user_func_array($route->getAction(), $args) ?? '',
            200
        );
    }

    /**
     * Retrieves the base path of the site for route matching.
     */
    private function getPath():string {
        $parts = explode('/', trim(get_bloginfo('url'), '/'));
        $basePath = implode('/', array_slice($parts, 3));

        return $basePath ? '/' . $basePath . '/' : '/';
    }

    /**
     * Decodes JSON payload from the request, if present.
     */
    private function getJsonPayload(): array {
        $body = $_REQUEST['payload'] ?? null;

        if (empty($body)) {
            return [];
        }

        $payload = json_decode($body, true) ?? json_decode(wp_unslash($body), true);

        return !empty($payload) ? $payload : [];
    }
}
