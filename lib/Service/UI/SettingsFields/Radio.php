<?php
/**
 * Settings Radio Field
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */

namespace Netdust\Service\UI\SettingsFields;

use Netdust\Service\UI\SettingsField;

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