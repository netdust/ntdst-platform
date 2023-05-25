<?php
/**
 * Make Class Trait.
 *
 * @since   1.3.0
 */

namespace Netdust\Traits;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


trait Classes {

	public static function make_class( $value, $factory ) {
		// If the value is a string, assume it's a class reference.

		if ( is_string( $value ) ) {
			$class = new $value;

			// If the value is an array, the class still needs defined.
		} elseif ( is_array( $value ) ) {

			// If the class is specified, construct the class from the specified value.
			if ( isset( $value['class'] ) ) {

				$class = $value['class'];
				$args  = isset( $value['args'] ) ? $value['args'] : [];

				// Otherwise, fallback to the default, and use the value as an array of arguments for the default.
			} else {
				$class = $factory;
				$args  = $value;
			}

			$is_assoc = count( array_filter( array_keys( $args ), 'is_string' ) ) > 0;
			// Convert single-level associative array to first argument using the array.
			if ( $is_assoc ) {
				$args = [ $args ];
			}

			$class = new $class( ...$args );

		} else {
            // Otherwise, assume the class is already instantiated, and return it directly.
			$class = $value;
		}

		return $class;
	}


	public function has_trait( $trait, $class ) {

        if( in_array($trait, class_uses($class)) ) {
            return true;
        }

		return false;
	}


}