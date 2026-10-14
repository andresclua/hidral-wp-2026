<?php
return array(
    'label'  => 'List Of',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::bg_color(array(
            'name'      => 'bg_color',
            'label'     => 'Background Color',
            'show_when' => array('any_of' => array(
                array('list_type', '==', 'card_with_image'),
                array('list_type', '==', 'card_with_icon'),
                array('list_type', '==', 'card_simple'),
            )),
        )),
        ACF_Builder::title(),
        ACF_Builder::subtitle(),
        ACF_Builder::select(array(
            'name'          => 'list_type',
            'label'         => 'List Type',
            'choices'       => array(
                'empty'          => 'Empty',
                'services'       => 'Services',
                'card_with_image' => 'Card with Image',
                'card_with_icon'  => 'Card with Icon',
                'card_simple'     => 'Card Simple',
            ),
            'default_value' => 'empty',
            'allow_null'    => 0,
        )),
        ACF_Builder::select(array(
            'name'          => 'items_per_row',
            'label'         => 'Items per Row',
            'choices'       => array(
                '3' => '3',
                '4' => '4',
            ),
            'default_value' => '4',
            'allow_null'    => 0,
            'show_when'     => array('any_of' => array(
                array('list_type', '==', 'services'),
                array('list_type', '==', 'card_with_image'),
                array('list_type', '==', 'card_with_icon'),
            )),
        )),
        ACF_Builder::select(array(
            'name'          => 'text_align',
            'label'         => 'Text Align',
            'choices'       => array(
                'left'   => 'Left',
                'center' => 'Center',
            ),
            'default_value' => 'left',
            'allow_null'    => 0,
            'show_when'     => array('any_of' => array(
                array('list_type', '==', 'services'),
                array('list_type', '==', 'card_with_image'),
                array('list_type', '==', 'card_with_icon'),
                array('list_type', '==', 'card_simple'),
            )),
        )),
        ACF_Builder::relationship(array(
            'name'      => 'services',
            'label'     => 'Services',
            'post_type' => array('servicios'),
            'show_when' => array('list_type', '==', 'services'),
        )),
        ACF_Builder::repeater(array(
            'name'         => 'items_image',
            'label'        => 'Items',
            'button_label' => 'Add Item',
            'layout'       => 'block',
            'show_when'    => array('list_type', '==', 'card_with_image'),
            'fields'       => array_merge(
                ACF_Builder::image(array('name' => 'image', 'label' => 'Image')),
                ACF_Builder::title(),
                ACF_Builder::text(array('name' => 'description', 'label' => 'Description'))
            ),
        )),
        ACF_Builder::repeater(array(
            'name'         => 'items_icon',
            'label'        => 'Items',
            'button_label' => 'Add Item',
            'layout'       => 'block',
            'show_when'    => array('list_type', '==', 'card_with_icon'),
            'fields'       => array_merge(
                ACF_Builder::image(array('name' => 'image', 'label' => 'Icon')),
                ACF_Builder::title(),
                ACF_Builder::text(array('name' => 'description', 'label' => 'Description'))
            ),
        )),
        ACF_Builder::repeater(array(
            'name'         => 'items_simple',
            'label'        => 'Items',
            'button_label' => 'Add Item',
            'layout'       => 'block',
            'show_when'    => array('list_type', '==', 'card_simple'),
            'fields'       => array_merge(
                ACF_Builder::title(),
                ACF_Builder::subtitle()
            ),
        )),
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
        ))
    ),
);
