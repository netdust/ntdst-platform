<?php
/**
 * Settings Radio Field
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */

namespace Netdust\View\UI\SettingsFields;

use Netdust\View\UI\SettingsField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Radio
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Radio extends SettingsField {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'radio';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {
		return (string) $value;
	}
}