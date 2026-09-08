<?php
return array(
    'title'  => '404 page',
    'fields' => array_merge(
        ACF_Builder::title(array('name' => '404_title', 'label' => 'Title')),
        ACF_Builder::text(array('name' => '404_subtitle', 'label' => 'Subtitle')),
        ACF_Builder::link(array('name' => '404_button', 'label' => 'Button')),
    ),
);
