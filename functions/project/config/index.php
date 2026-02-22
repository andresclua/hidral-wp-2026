<?php

return [
  // default
  'image_sizes' => require __DIR__ . '/default_config.php',
  'image_type' => ['generate_image_tag', 'render_wp_image'],
  'enable_search_modules' => true,
  'enable_vulnerability' => true,
   

  // project
  'post_types'  => require __DIR__ . '/post-types_config.php',
  'taxonomies'  => require __DIR__ . '/taxonomy_config.php',
  'endpoint' =>  require __DIR__ . '/endpoint_config.php',
  'ajax'     =>  require __DIR__ . '/ajax_config.php',
  'admin_controller' => require __DIR__ . '/admin-controller_config.php',

  //blocks
  'default_blocks' => require __DIR__ . '/default-blocks_config.php',
  'custom_blocks' => require __DIR__ . '/custom-blocks_config.php',

  // file Paths
  'local_variable' => 'functions/project/deploy/local-variable.php',
  'hash' => 'functions/project/deploy/hash.php', 
  'enqueues' => 'functions/project/deploy/enqueues.php',

  //redirect pages / ids
  'redirect_pages' => ['34434','45'],
  'redirect_single_pages' => ['1'],
  'redirect_tax_pages' => ['insight-type', 'category'],

  'mail_to_config' => [
    'email' => 'andresclua@gmail.com',
    'subject' => 'test',
    'message' => 'test',
  ],

  // 'grammar' => [
  //   'post_types' => ['trabajo', 'page'],  // Los post types que quieres revisar
  //   'notify_emails' => ['nerea@terrahq.com', 'eli@terrahq.com', 'andres@terrahq.com'], // Emails para notificaciones
  //   'language' => 'en-US'               // Idioma para la revisión
  // ],

];

