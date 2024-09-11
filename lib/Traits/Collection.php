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
     * @return Collection
     */
    public function add(string $name, mixed $value): Collection
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
     * @return Collection
     */
    public function remove(string $name): Collection
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
        if (!$this->exists($name)) {
            throw new LogicException('The collection item "' . $name . '" was not found.');
        }

        return $this->collection[$name];
    }

    /**
     * Check if a collection item exists.
     * @param  string  $name
     * @return bool
     */
    public function exists($name): bool
    {
        return isset($this->collection[$name]);
    }
}