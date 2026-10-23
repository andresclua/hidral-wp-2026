<?php
return array(
    'title'  => 'Header',
    'fields' => array_merge(
        ACF_Builder::image(array('name' => 'header_logo', 'label' => 'Logo')),
        ACF_Builder::repeater(array(
            'name'         => 'header_nav',
            'label'        => 'Navigation',
            'button_label' => 'Add Link',
            'layout'       => 'table',
            'fields'       => array_merge(
                ACF_Builder::text(array('name' => 'label', 'label' => 'Label')),
                ACF_Builder::post_object(array(
                    'name'      => 'page',
                    'label'     => 'Page',
                    'post_type' => array('page'),
                )),
            ),
        )),
        ACF_Builder::custom_button(array(
            'name'  => 'header_button_primary',
            'label' => 'Primary Button (solid blue, e.g. WhatsApp)',
            'types' => array('link'),
        )),
        ACF_Builder::custom_button(array(
            'name'  => 'header_button_secondary',
            'label' => 'Secondary Button (outline, e.g. Llamar)',
            'types' => array('link'),
        )),
    ),
);
