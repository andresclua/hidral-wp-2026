<?php
return array(
    'title'  => 'Gallery Grid',
    'fields' => ACF_Builder::repeater(array(
        'name'         => 'gallery_grid_images',
        'label'        => 'Images',
        'button_label' => 'Add Image',
        'layout'       => 'table',
        'fields'       => ACF_Builder::image(array('name' => 'image', 'label' => 'Image', 'mime_types' => 'jpg,jpeg,png,webp')),
    )),
);
