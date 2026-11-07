<?php
return array(
    'label'  => 'Gallery Grid',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::bg_color(),
        ACF_Builder::select(array(
            'name'          => 'source',
            'label'         => 'Images From',
            'choices'       => array(
                'module'          => 'This module',
                'general_options' => 'General Options',
            ),
            'default_value' => 'module',
            'allow_null'    => 0,
        )),
        ACF_Builder::repeater(array(
            'name'         => 'images',
            'label'        => 'Images',
            'button_label' => 'Add Image',
            'layout'       => 'table',
            'show_when'    => array('source', '==', 'module'),
            'fields'       => ACF_Builder::image(array('name' => 'image', 'label' => 'Image', 'mime_types' => 'jpg,jpeg,png,webp')),
        )),
        ACF_Builder::note(array(
            'name'      => 'images_note',
            'label'     => '',
            'message'   => 'The images are taken from General Options → Gallery Grid.',
            'show_when' => array('source', '==', 'general_options'),
        ))
    ),
);
