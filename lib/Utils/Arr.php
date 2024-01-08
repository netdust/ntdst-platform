<?php

namespace Netdust\Utils;

use ArrayAccess;

class Arr {

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param  mixed  $value
	 * @return bool
	 */
	public static function accessible($value): bool
	{
		return \is_array($value) || $value instanceof ArrayAccess;
	}

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function add($array, $key, $value): array
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
    public static function set(&$array, $key, $value): array
    {
        $array[$key] = $value;

        return $array;
    }


	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	public static function divide($array): array
	{
		return [\array_keys($array), \array_values($array)];
	}


    /**
     * Get an item from an array.
     *
     * @param \ArrayAccess|array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(ArrayAccess|array $array, string $key, mixed $default = null): mixed
    {
	    if (! static::accessible($array)) {
		    return $default;
	    }

	    if (\is_null($key)) {
		    return $array;
	    }

	    if (static::exists($array, $key)) {
		    return $array[$key];
	    }

        return $default;
    }

	/**
	 * Get the first element in an array passing a given truth test.
	 *
	 * @param  array  $array
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function first(array $array, callable|null $callback = null, mixed $default = null): mixed
	{
		if (\is_null($callback)) {
			if (empty($array)) {
				return $default;
			}

			foreach ($array as $item) {
				return $item;
			}
		}

		foreach ($array as $key => $value) {
			if (\call_user_func($callback, $value, $key)) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Get the last element in an array passing a given truth test.
	 *
	 * @param  array  $array
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function last(array $array, callable|null $callback = null, mixed $default = null): mixed
	{
		if (\is_null($callback)) {
			return empty($array) ? $default : \end($array);
		}

		return static::first(\array_reverse($array, true), $callback, $default);
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param  \ArrayAccess|array  $array
	 * @param  string|int  $key
	 * @return bool
	 */
	public static function exists(ArrayAccess|array $array, string|int $key): bool
    {
	    if ($array instanceof ArrayAccess) {
		    return $array->offsetExists($key);
	    }

	    return \array_key_exists($key, $array);
    }

    /**
     * Remove an attribute from the collection
     *
     * @param array $array
     * @param string $key   The name of the parameter
     * @return void
     */
    public static function remove(ArrayAccess|array $array, string|int $key): void
    {
        unset($array[$key]);
    }

	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param  array  $array
	 * @return bool
	 */
	public static function isAssoc(array $array)
	{
		$keys = \array_keys($array);

		return \array_keys($keys) !== $keys;
	}

}