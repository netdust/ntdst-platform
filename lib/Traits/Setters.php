<?php

namespace Netdust\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use mysql_xdevapi\Exception;
use Netdust\Factories\Logger;

trait Setters {

    /**
     * Factory method.
     *
     * @since 3.0.0
     *
     * @param array $args List of arguments used to create this menu page.
     */
    public function add( array &$args ) {
        $this->set_values( $args );
    }

	/**
	 * Loop through each argument, set the value, and remove the value if it was already set.
	 *
	 * @since 1.2.0
	 *
	 * @param array $args Arguments to set, and manipulate.
	 */
	protected function set_values( array &$args ) {
		// Override default params.
		foreach ( $args as $arg => $value ) {
            try { $this->{$arg} = $value; } catch (Exception $e){};
            unset( $args[ $arg ] );
		}
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
	protected function set_callable( $callable, ...$args ) {
		if ( is_callable( $callable ) ) {
			return call_user_func( $callable, ...$args );
		}

		return Logger::warning(
            'The provided callback is invalid',
			'invalid_callback',
			[
				'callback' => $callable,
				'stack'    => debug_backtrace(),
			]
		);
	}

}