<?php
/**
 * Settings Checkbox Field.
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */

namespace Netdust\Loaders\Admin\Factories\SettingsFields;

use Netdust\Loaders\Admin\Abstracts\SettingsField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Checkbox
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Checkbox extends SettingsField {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'checkbox';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {
		return (boolean) $value;
	}

}