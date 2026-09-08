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
                ACF_Builder::url(array('name' => 'url', 'label' => 'URL')),
            ),
        )),
    ),
);
