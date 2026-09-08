<?php
/**
 * Flexible Modules Configuration
 *
 * Each layout lives in its own file for maintainability.
 * The filename becomes the layout name (e.g. accordion.php => 'accordion').
 *
 * @see ACF_Builder for available islands
 * @see Flexible_Content for registration
 */

$layouts = array();
foreach (glob(__DIR__ . '/*.php') as $file) {
    $basename = basename($file, '.php');
    if ($basename === 'index') {
        continue;
    }
    $layouts[$basename] = require $file;
}

return array(
    'title'        => 'Flexible Modules',
    'name'         => 'modules',
    'button_label' => 'Add Module',

    'location' => array(
        array('page_template' => 'page-modules.php'),
    ),

    'layouts' => $layouts,
);
