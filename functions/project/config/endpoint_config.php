<?php

return [
  [
    'namespace' => 'wp/v2/tf_api',
    'route' => '/another-endpoint',
    'method' => 'GET',

    // 🔹 SANITIZATION HAPPENS HERE
    'args' => [
      // STRING
      'q' => [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
      ],
      // NUMBER
      'page' => [
        'default' => 1,
        'sanitize_callback' => 'absint',
        'validate_callback' => function ($value) {
          return $value >= 1;
        },
      ],

      // ID
      'post_id' => [
        'required' => false,
        'sanitize_callback' => 'absint',
        'validate_callback' => function ($value) {
          return $value > 0;
        },
      ],
    ],

    'callback' => function (WP_REST_Request $request) {
      return [
        'ok' => true,

        // YA VIENEN SANITIZADOS
        'q'       => $request->get_param('q'),       // string limpio
        'page'    => $request->get_param('page'),    // int >= 1
        'post_id' => $request->get_param('post_id'), // int > 0
      ];
    },
  ],

  [
    'namespace' => 'wp/v2/tf_api',
    'route' => '/status',
    'method' => 'GET',

    // Este endpoint no recibe input → no necesita args
    'callback' => function () {
      return [
        'ok' => true,
        'status' => 'running',
        'time' => current_time('mysql'),
      ];
    },
  ],
];
