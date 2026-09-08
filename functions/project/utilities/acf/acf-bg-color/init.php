<?php
/**
 * Registration logic for the bg_color ACF field type.
 *
 * Reads color options from ACF_Builder::get_bg_color_config() and passes
 * them to the JS via wp_localize_script so each project can define its
 * own color palette.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'terra_include_acf_field_bg_color' );

function terra_include_acf_field_bg_color() {
	if ( ! function_exists( 'acf_register_field_type' ) ) {
		return;
	}

	require_once __DIR__ . '/class-terra-acf-field-bg-color.php';
	acf_register_field_type( 'Terra_ACF_Field_Bg_Color' );
}
