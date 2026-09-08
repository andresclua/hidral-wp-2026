<?php
/**
 * Scripts options tab.
 *
 * Injects raw code site-wide. Field names match the output already wired in
 * header.php (header_scripts, body_top_scripts) and footer.php
 * (body_bottom_scripts). Output is echoed raw, so only trusted users should edit.
 */

$fields = array_merge(
    ACF_Builder::text(array('name' => 'header_scripts', 'label' => 'Header Scripts', 'rows' => 6)),
    ACF_Builder::text(array('name' => 'body_top_scripts', 'label' => 'Body Top Scripts', 'rows' => 6)),
    ACF_Builder::text(array('name' => 'body_bottom_scripts', 'label' => 'Body Bottom Scripts', 'rows' => 6)),
);

return array(
    'title'  => 'Scripts',
    'fields' => $fields,
);
