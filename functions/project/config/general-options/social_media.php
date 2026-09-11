<?php
return array(
    'title'  => 'Social Media',
    'fields' => ACF_Builder::repeater(array(
        'name'         => 'social_links',
        'label'        => 'Social Links',
        'button_label' => 'Add Social Network',
        'layout'       => 'table',
        'fields'       => array_merge(
            ACF_Builder::select(array(
                'name'    => 'platform',
                'label'   => 'Platform',
                'choices' => array(
                    'facebook'  => 'Facebook',
                    'instagram' => 'Instagram',
                    'x'         => 'X / Twitter',
                    'linkedin'  => 'LinkedIn',
                    'youtube'   => 'YouTube',
                ),
                'width' => '40',
            )),
            ACF_Builder::url(array(
                'name'  => 'url',
                'label' => 'URL',
                'width' => '60',
            ))
        ),
    )),
);
