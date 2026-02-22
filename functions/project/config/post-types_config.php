<?php

return [
  [
    'post_type' => 'trabajos',
    'singular_name' => 'Trabajo',
    'plural_name' => 'Trabajos',
    'args' => [
      'menu_icon' => 'dashicons-portfolio',
      'rewrite' => ['slug' => 'trabajos', 'with_front' => false],

      // Supports: Solo título (ACF lo maneja todo)
      'supports' => ['title'],

      'terra_hide_permalink' => true,
      'terra_hide_preview_button' => true,
      'terra_hide_seo_columns' => true,

      // terra_redirect: Redirigir a URL externa de ACF
      'terra_redirect' => function($post_id, $post) {
        // Intenta obtener el campo ACF 'external_link'
        $external_url = get_field('external_link', $post_id);

        // Si existe y no está vacío, redirige ahí
        if ($external_url) {
          return $external_url;
        }

        // Si no hay URL externa, redirige al archivo de trabajos
        return '/trabajos';
      },
    ],
  ],
  [
    'post_type' => 'servicios',
    'singular_name' => 'Servicio',
    'plural_name' => 'Servicios',
    'args' => [
      'menu_icon' => 'dashicons-cloud-saved',
      'rewrite' => ['slug' => 'servicios', 'with_front' => false],
      'supports' => ['title', 'thumbnail', 'excerpt', 'revisions'],
      'terra_hide_permalink' => false,
      'terra_hide_preview_button' => false,
      'terra_hide_seo_columns' => true,
    ],
  ],
];