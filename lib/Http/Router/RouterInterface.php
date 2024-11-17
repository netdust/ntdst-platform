<?php

namespace Netdust\Http\Router;

interface RouterInterface {

    public function match(string $requestUri, string $method);

    public function map(array $methods, string $uri, $callback);

}