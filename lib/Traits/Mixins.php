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
        return ( isset($this->mixins[$name]) && $this->mixins[$name] instanceof \Closure );
    }

    public function callMixin($name, ...$arguments): mixed
    {
        if (!$this->hasMixin($name)) {
            return app()->get( LoggerInterface::class )->warning(
                'The provided method is invalid',
                'invalid_method',
                [
                    'callback' => $name,
                    'class'=>get_class($this)
                ]
            );
        }

        $mixin = $this->mixins[$name];
        return ( $mixin(...$arguments) );
    }

    public function extend( $mixinInstance, $replace = true ) {

        // Optionally, if the mixin *itself* has methods that should be directly accessible:
        $reflection = new \ReflectionClass($mixinInstance);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED ) as $method) {
            if ($method->getName() !== '__construct' && ($replace || ! $this->hasMixin($method->name)) ) {
                $this->mixins[$method->getName()] = function( ...$args ) use ( $mixinInstance, $method ) {
                    return $method->invoke($mixinInstance, ...$args );
                };
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

        if (is_callable(['parent', '__call'])){
            return parent::__call($method, $parameters);
        }

        return $this->callMixin( $method, ...$parameters );
    }

}