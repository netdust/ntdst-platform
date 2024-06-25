<?php

namespace Netdust\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

trait Mixins {

    protected array $mixins = [];

    public function mixin($name, $mixin) {
        $this->mixins[$name] = $mixin;
    }

    public function hasMixin($name)
    {
        return !empty($this->mixins[$name]);
    }

    public function extend( $mixin, $replace = true ) {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || ! $this->hasMixin($method->name)) {
                $this->mixin($method->name, function( ...$args ) use ( $mixin, $method ) {
                    return $method->invoke($mixin, ...$args );
                });
            }
        }
    }

    /**
     * Flush the existing macros.
     *
     * @return void
     */
    public function flushMixins()
    {
        $this->mixins = [];
    }

    public function __call($method, $parameters)
    {
        if (! $this->hasMixin($method)) {
            return app()->make( LoggerInterface::class )->warning(
                'The provided method is invalid',
                'invalid_method',
                [
                    'callback' => $method,
                    'stack'    => debug_backtrace(),
                ]
            );
        }

        $mixin = $this->mixins[$method];
        return $mixin(...$parameters);
    }

}