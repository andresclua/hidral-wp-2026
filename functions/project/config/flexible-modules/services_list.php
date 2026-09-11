<?php
return array(
    'label'  => 'Services List',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::title(),
        ACF_Builder::title(array('name' => 'subtitle', 'label' => 'Subtitle')),
        ACF_Builder::relationship(array(
            'name'      => 'services',
            'label'     => 'Services',
            'post_type' => array('servicios'),
        ))
    ),
);
