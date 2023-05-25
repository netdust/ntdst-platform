<?php
/**
 * Admin Section Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */

use Netdust\Loaders\Admin\Abstracts\AdminSection;
use Netdust\Loaders\Admin\Abstracts\SettingsField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof AdminSection ) {
	return;
}

foreach ( $template->fields as $key => $field ) {
	$field = $template->get_field( $key );
	
	if ( $field instanceof SettingsField ) {
		echo $field->place( true );
	}
}
