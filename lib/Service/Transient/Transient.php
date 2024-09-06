<?php
/**
 * Registers a shortcode
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Netdust\Service\Transient;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Cache
 *
 */
class Transient {

    protected string $name;
    protected int $expiry;
    protected mixed $default;

    public function __construct(string $name, int $expiry = 0, mixed $default = null)
    {

        $this->name = $name;
        $this->expiry = $expiry;
        $this->default = $default;
    }

    /** @inheritDoc */
    protected function read()
    {
        $value = get_transient($this->name);

        return ($value === false) ? null : $value;
    }

    /** @inheritDoc */
    protected function write($value): bool
    {
        return set_transient($this->name, $value, $this->expiry);
    }

    /**
     * Deletes the transient.
     *
     * @return bool True if the transient was updated, false otherwise.
     */
    public function delete(): bool
    {
        return delete_transient($this->name);
    }

}