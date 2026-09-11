<?php
return array(
    'label'  => 'Media Hero',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::title(),
        ACF_Builder::text(array('name' => 'text', 'label' => 'Text')),
        ACF_Builder::image(array('name' => 'image', 'label' => 'Image')),
        ACF_Builder::custom_button(array('types' => array('link')))
    ),
);
