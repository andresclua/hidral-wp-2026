<?php
return array(
    'label'  => 'Feature List',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::bg_color(array('name' => 'bg_color', 'label' => 'Background Color')),
        ACF_Builder::title(),
        ACF_Builder::title(array('name' => 'subtitle', 'label' => 'Subtitle')),
        ACF_Builder::repeater(array(
            'name'         => 'features',
            'label'        => 'Features',
            'button_label' => 'Add Feature',
            'layout'       => 'block',
            'fields'       => array_merge(
                ACF_Builder::image(array('name' => 'image', 'label' => 'Image')),
                ACF_Builder::title(array('name' => 'title', 'label' => 'Title')),
                ACF_Builder::title(array('name' => 'description', 'label' => 'Description'))
            ),
        )),
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
        ))
    ),
);
