<?php
/**
 * Settings Text Field
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
 * Class Text
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Textarea extends SettingsField {

    /**
     * @inheritDoc
     */
    function get_field_type() {
        return 'textarea';
    }

    /**
     * @inheritDoc
     */
    function sanitize( $value ) {
        return (string) $value;
    }
}