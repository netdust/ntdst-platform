<?php

namespace Netdust\Utils;

use ArrayAccess;

class Arr {

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function add($array, $key, $value)
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Set an attribute of the collection
     *
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        $array[$key] = $value;

        return $array;
    }


    /**
     * Get an item from an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        return $default;
    }

    public static function exists($array, $key)
    {
        // Don't use "isset", since it returns false for null values
        return array_key_exists($key, $array);
    }

    /**
     * Remove an attribute from the collection
     *
     * @param array $array
     * @param string $key   The name of the parameter
     * @return void
     */
    public static function remove($array, $key)
    {
        unset($array[$key]);
    }

}