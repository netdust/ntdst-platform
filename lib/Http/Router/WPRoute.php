<?php

namespace Netdust\Http\Router;


class WPRoute
{
    private string $uri;
    private array $methods = [];
    private mixed $action;
    private string $name = '';
    private array $roles = [];
    private array $nonce = [];

    public function __construct(array $methods, string $uri, mixed $action)
    {
        $this->methods = $methods;
        $this->setUri($uri);
        $this->setAction($action);
    }

    private function setUri(string $uri): void
    {
        $this->uri = rtrim($uri, ' /');
    }

    private function setAction(mixed $action): void
    {
        // Check if this looks like it could be a class/method string
        if (!is_callable($action) && is_string($action)) {
            $action = $this->convertClassStringToClosure($action);
        }

        $this->action = $action;
    }

    private function convertClassStringToClosure($string): mixed
    {
        @list($className, $method) = explode('@', $string);

        if (!isset($className) || !isset($method)) {
            throw new RouteClassStringParseException('Could not parse route controller from string: `' . $string . '`');
        }

        if (!class_exists($className)) {
            throw new RouteClassStringControllerNotFoundException('Could not find route controller class: `' . $className . '`');
        }

        if (!method_exists($className, $method)) {
            throw new RouteClassStringMethodNotFoundException('Route controller class: `' . $className . '` does not have a `' . $method . '` method');
        }

        return function ($params = null, $request = null) use ($className, $method) {
            $controller = new $className;
            return $controller->$method($params, $request);
        };
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getAction(): mixed
    {
        return $this->action;
    }

    public function name(string $name): WPRoute
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function roles(array $roles): WPRoute
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function nonce(array $nonce): WPRoute
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function getNonce(): array
    {
        return $this->nonce;
    }
}