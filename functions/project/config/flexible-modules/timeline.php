<?php
return array(
    'label'  => 'Timeline',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::title(),
        ACF_Builder::repeater(array(
            'name'         => 'buttons',
            'label'        => 'Buttons',
            'button_label' => 'Add Button',
            'layout'       => 'block',
            'fields'       => ACF_Builder::custom_button(array(
                'name'  => 'button',
                'label' => 'Button',
                'types' => array('link'),
            )),
        )),
        ACF_Builder::repeater(array(
            'name'         => 'items',
            'label'        => 'Items',
            'button_label' => 'Add Item',
            'layout'       => 'block',
            'fields'       => array_merge(
                // ACF_Builder::image(array('name' => 'icon', 'label' => 'Icon')),
                ACF_Builder::title(),
                ACF_Builder::title(array('name' => 'description', 'label' => 'Description'))
            ),
        ))
    ),
);
