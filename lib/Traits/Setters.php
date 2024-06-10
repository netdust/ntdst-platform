<?php

namespace Netdust\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use Exception;
use Netdust\Factories\Logger;
use Netdust\Logger\LoggerInterface;

trait Setters {

	protected array $properties = [];

	/**
	 * Loop through each argument, set the value, and remove the value if it was already set.
	 *
	 * @since 1.2.0
	 *
	 * @param array $args Arguments to set, and manipulate.
	 */
	protected function set_values( array &$args ): void {
		$this->setProperties( $args );
	}

    protected function setProperties( array &$args ): void {
        // Override default params.
        foreach ( $args as $name  => $value ) {
            if( $this->hasProperty($name) ) {
                $this->{$name} = $value;
            }
            else {
                $this->properties[$name] = $value;
            }
            unset( $args[ $name ] );
        }
    }

    public function hasProperty($name) {
        return property_exists($this, $name) || array_key_exists($name, $this->properties);
    }

    public function getProperty($name) {
        if( $this->hasProperty($name) )
            return $this->{$name};
        if( key_exists( $name, $this->properties ) )
            return $this->properties[$name];
        return null;
    }

    /**
	 * Set a custom callback from the provided argument, and set or arguments.
	 *
	 * @since 1.2.0
	 *
	 * @param callable $callable The callback
	 * @param mixed ...$args The arguments to pass to the callback
	 *
	 * @return false|mixed|\WP_Error
	 */
	protected function set_callable( callable $callable, mixed ...$args ): mixed {
		if ( is_callable( $callable ) ) {
			return call_user_func( $callable, ...$args );
		}

		return app()->make( LoggerInterface::class )->warning(
            'The provided callback is invalid',
			'invalid_callback',
			[
				'callback' => $callable,
				'stack'    => debug_backtrace(),
			]
		);
	}

}