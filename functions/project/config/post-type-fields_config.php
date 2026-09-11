<?php
/**
 * Post Type Fields Configuration
 *
 * ACF field groups attached to specific post types.
 * Each entry registers a field group via acf_add_local_field_group().
 * Fields use ACF_Builder islands for consistency.
 *
 * @package TerraProject
 */

return [
    [
        'title'     => 'Service Details',
        'post_type' => 'servicios',
        'fields'    => array_merge(
            ACF_Builder::select(array(
                'name'          => 'service_category',
                'label'         => 'Category',
                'choices'       => array(
                    'bombas_de_agua' => 'Bombas de Agua',
                    'canerias'       => 'Cañerías',
                    'seguridad'      => 'Seguridad',
                ),
                'return_format' => 'label',
            )),
            ACF_Builder::text(array(
                'name'         => 'card_description',
                'label'        => 'Card Description',
                'instructions' => 'Short text shown on the Services List card. Not displayed on the single service page.',
                'rows'         => 2,
            )),
        ),
    ],
];