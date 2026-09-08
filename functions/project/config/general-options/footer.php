<?php
return array(
    'title'  => 'Footer',
    'fields' => array_merge(
        ACF_Builder::text(array('name' => 'footer_text', 'label' => 'Footer Text')),
        ACF_Builder::title(array('name' => 'copyright_text', 'label' => 'Copyright Text')),
    ),
);
