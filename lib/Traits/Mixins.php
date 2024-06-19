<?php

namespace Netdust\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Netdust\Logger\LoggerInterface;

trait Mixins {

    protected array $mixins = [];

    public function mixin($name, $mixin) {
        $this->mixins[$name] = $mixin;
    }

    public function hasMixin($name)
    {
        return !empty($this->mixins[$name]);
    }

    /**
     * Flush the existing macros.
     *
     * @return void
     */
    public function flushMacros()
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