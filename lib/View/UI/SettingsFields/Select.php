<?php
/**
 * Settings Select Field
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
 * Class Select
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Select extends SettingsField {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'select';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {
		return (string) $value;
	}
}