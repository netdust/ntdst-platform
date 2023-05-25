<?php

namespace Netdust\Utils\Router;

interface RouterInterface {

    public function match(string $requestUri, string $method);

    public function map(array $methods, string $uri, $callback, $name);

    public function addRoute(array $route);

}