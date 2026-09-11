<?php
return array(
    'label'  => 'Centered Hero',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::title(),
        ACF_Builder::title(array('name' => 'subtitle', 'label' => 'Subtitle')),
        ACF_Builder::custom_button(array(
            'name'  => 'primary_button',
            'label' => 'Primary Button',
            'types' => array('link'),
        )),
        ACF_Builder::boolean(array(
            'name'  => 'has_secondary_button',
            'label' => 'Add Secondary Button?',
        )),
        ACF_Builder::custom_button(array(
            'name'      => 'secondary_button',
            'label'     => 'Secondary Button',
            'types'     => array('link'),
            'show_when' => array('has_secondary_button', '==', 1),
        )),
        ACF_Builder::image(array('name' => 'image', 'label' => 'Image'))
    ),
);
