<?php
return array(
    'label'  => 'Sidenav',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::repeater(array(
            'name'         => 'sidenav_items',
            'label'        => 'Sidenav Items',
            'button_label' => 'Add Item',
            'layout'       => 'block',
            'fields'       => array_merge(
                ACF_Builder::title(array('name' => 'title', 'label' => 'Title')),
                ACF_Builder::wysiwyg(array('name' => 'content', 'label' => 'Content')),
            ),
        )),
    ),
);
