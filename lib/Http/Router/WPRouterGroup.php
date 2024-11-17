<?php

namespace Netdust\Http\Router;

class WPRouterGroup
{

    protected RouterInterface $router;
    protected string $prefix;

    public function __construct(string $prefix, RouterInterface $router)
    {
        $this->prefix = trim($prefix, ' /');
        $this->router = $router;
    }

    private function appendPrefixToUri(string $uri): string  {
        return $this->prefix . '/' . $uri;
    }

    public function map(array $verbs, string $uri, $callback) : WPRoute
    {
        return $this->router->map($verbs, $this->appendPrefixToUri($uri), $callback);
    }

    public function virtual(string $uri, $virtualPage, $callback=null) : WPRoute
    {
        return $this->router->virtual($this->appendPrefixToUri($uri), $virtualPage, $callback);
    }

    public function group($prefix, $callback) : WPRouterGroup
    {
        $group = new WPRouterGroup($this->appendPrefixToUri($prefix), $this->router);

        call_user_func($callback, $group);

        return $this;
    }
}