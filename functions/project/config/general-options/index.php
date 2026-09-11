<?php
/**
 * General Options Configuration
 *
 * Each group lives in its own file for maintainability.
 * The filename becomes the group name (e.g. social_media.php => 'social_media').
 *
 * @see ACF_Builder for available islands
 * @see Options_Page for registration
 */

$tab_order = array('header', 'site_identity', 'footer', 'social_media', 'trusted_companies', 'contact', 'api_keys', 'scripts', '404_page');

$groups = array();
foreach ($tab_order as $tab) {
    $file = __DIR__ . '/' . $tab . '.php';
    if (file_exists($file)) {
        $group = require $file;
        // Skip tabs with no fields (e.g. terra-only tabs for non-terra users)
        if (!empty($group['fields'])) {
            $groups[$tab] = $group;
        }
    }
}

return array(
    'page_title'  => 'General Options',
    'menu_title'  => 'General Options',
    'menu_slug'   => 'general-options',
    'capability'  => 'edit_posts',
    'parent_slug' => 'terra_dashboard',
    'position'    => 0,

    'groups' => $groups,
);
