<?php
/**
 * Terra UI Components
 *
 * Reusable admin UI components for the framework.
 * Enqueue with terra_enqueue_ui('tabs') from any admin page.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue a Terra UI component (CSS + JS)
 *
 * @param string $component Component name (e.g. 'tabs')
 */
function terra_enqueue_ui($component) {
    $base_dir = __DIR__;
    $base_url = str_replace(get_template_directory(), get_template_directory_uri(), $base_dir);

    $css_file = $base_dir . '/' . $component . '.css';
    $js_file  = $base_dir . '/' . $component . '.js';

    if (file_exists($css_file)) {
        wp_enqueue_style(
            'terra-ui-' . $component,
            $base_url . '/' . $component . '.css',
            [],
            filemtime($css_file)
        );
    }

    if (file_exists($js_file)) {
        wp_enqueue_script(
            'terra-ui-' . $component,
            $base_url . '/' . $component . '.js',
            [],
            filemtime($js_file),
            true
        );
    }
}
