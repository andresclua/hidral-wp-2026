<?php
return array(
    'title'  => 'Footer',
    'fields' => array_merge(
        ACF_Builder::title(array('name' => 'copyright_text', 'label' => 'Copyright Text')),
        ACF_Builder::title(array('name' => 'footer_tagline', 'label' => 'Tagline', 'default_value' => 'el valor de estar cerca')),
    ),
);
