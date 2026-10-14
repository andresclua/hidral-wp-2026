<?php
return array(
    'label'  => 'Media + Text',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::pretitle(),
        ACF_Builder::title(),
        ACF_Builder::wysiwyg(array('name' => 'text', 'label' => 'Text')),
        ACF_Builder::custom_button(array('types' => array('link'))),
        ACF_Builder::image(array('name' => 'image', 'label' => 'Image'))
    ),
);
