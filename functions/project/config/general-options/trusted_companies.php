<?php
return array(
    'title'  => 'Trusted Companies',
    'fields' => array_merge(
        ACF_Builder::title(array('name' => 'trusted_companies_title', 'label' => 'Title')),
        ACF_Builder::repeater(array(
            'name'         => 'trusted_companies_images',
            'label'        => 'Companies',
            'button_label' => 'Add Company Logo',
            'layout'       => 'table',
            'max'          => 6,
            'fields'       => ACF_Builder::image(array('name' => 'image', 'label' => 'Logo')),
        )),
    ),
);
