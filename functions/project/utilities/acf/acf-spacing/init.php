<?php
/**
 * Spacing custom ACF field type.
 *
 * Options come from ACF_Builder::get_config('spacing').
 * This file lives in project so each project can customize the UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'terra_include_acf_field_spacing' );

function terra_include_acf_field_spacing() {
	if ( ! function_exists( 'acf_register_field_type' ) ) {
		return;
	}

	require_once __DIR__ . '/class-terra-acf-field-spacing.php';
	acf_register_field_type( 'Terra_ACF_Field_Spacing' );
}
