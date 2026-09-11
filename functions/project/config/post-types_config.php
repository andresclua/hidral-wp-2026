<?php

return [
  [
    'post_type' => 'servicios',
    'singular_name' => 'Servicio',
    'plural_name' => 'Servicios',
    'args' => [
      'menu_icon' => 'dashicons-cloud-saved',
      'rewrite' => ['slug' => 'servicios', 'with_front' => false],
      'supports' => ['title', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'],
      'terra_hide_permalink' => false,
      'terra_hide_preview_button' => false,
      'terra_hide_seo_columns' => true,
    ],
  ],
];
