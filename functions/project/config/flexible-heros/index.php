<?php
/**
 * Flexible Heros Configuration
 *
 * Each layout lives in its own file for maintainability.
 * The filename becomes the layout name (e.g. title_left_text_right_hero.php).
 * Limited to max 1 hero per page.
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
    'title'        => 'Flexible Heros',
    'name'         => 'heros',
    'button_label' => 'Add Hero',
    'max'          => 1,

    'location' => array(
        array('page_template' => 'page-modules.php'),
    ),

    'layouts' => $layouts,
);
