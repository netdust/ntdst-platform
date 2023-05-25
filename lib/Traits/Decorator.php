<?php

namespace Netdust\Traits;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Trait Decorator
 *
 */
trait Decorator
{
    public $decorated;
    
    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (is_callable([$this->decorated, $method])) {
            return call_user_func_array([$this->decorated, $method], $args);
        }

        throw new \Exception(
            sprintf("Call undefined method: %s::%s", get_class($this->decorated), $method));
    }

    /**
     * @param $property
     * @return null
     */
    public function __get($property)
    {
        $value = $this->decorated->{$property};
        if( isset( $value ) )
            return $value;
        return null;
    }

    /**
     * @param $property
     * @param $value
     * @return $this
     */
    public function __set($property, $value)
    {
        $this->decorated->{$property} = $value;
        return $this;
    }
}