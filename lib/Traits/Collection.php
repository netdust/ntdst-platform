<?php


namespace Netdust\Traits;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use LogicException;

/**
 * A collection of template folders.
 */
trait Collection
{
    /**
     * Collection array.
     * @var array
     */
    protected array $collection = array();

    /**
     * Add item to collection.
     * @param  string  $name
     * @param  mixed  $value
     * @return mixed
     */
    public function add(string $name, mixed $value): mixed
    {
        if ($this->exists($name)) {
            throw new LogicException('The collection item "' . $name . '" is already being used.');
        }

        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * Remove an item.
     * @param  string  $name
     * @return mixed
     */
    public function remove(string $name): mixed
    {
        if (!$this->exists($name)) {
            throw new LogicException('The collection item "' . $name . '" was not found.');
        }

        unset($this->collection[$name]);

        return $this;
    }

    /**
     * Get a collection item.
     * @param  string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->exists($name) ? $this->collection[$name] : null;
    }

    /**
     * Check if a collection item exists.
     * @param  string  $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->collection[$name]);
    }
}