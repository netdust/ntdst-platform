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
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all(): array {
        return $this->collection;
    }

    /**
     * Get all of the keys in the collection.
     *
     * @return array
     */
    public function keys(): array {
        return array_keys( $this->collection );
    }

    /**
     * Get all of the values in the collection.
     *
     * @return array
     */
    public function values(): array {
        return array_values( $this->collection );
    }


    /**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @return array
     */
    public function diff_keys( array $items ): array {
        return array_diff_key( $this->collection,  $items );
    }

    /**
     * Run a map over the collection.
     *
     * @return array
     */
    public function map( callable $callback ): array {
        $keys = array_keys( $this->collection );

        $items = array_map( $callback, $this->collection, $keys );

        return array_combine( $keys, $items );
    }

    /**
     * Check if a collection is empty.
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->collection);
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