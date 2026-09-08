<?php
/**
 * Project Configuration
 *
 * Central config file for the entire project. Each key maps to a feature
 * handled by a Terra Framework class. Edit the individual config files
 * to customize each feature.
 *
 * @package TerraProject
 */

return [

  // ---------------------------------------------------------------------------
  // Default Setup
  // Image sizes, image render functions, and framework module toggles
  // ---------------------------------------------------------------------------
  'image_sizes'            => require __DIR__ . '/default_config.php',       // Custom image sizes (thumbnail dimensions)
  'image_type'             => ['generate_image_tag', 'render_wp_image'],     // Which image functions to expose globally
  'enable_search_modules'  => true,                                          // Enable Search Modules admin panel
  'enable_search_gutenberg' => true,                                         // Enable Search Gutenberg admin panel
  'enable_vulnerability'   => true,                                          // Enable WP Vulnerability Checker

  // ---------------------------------------------------------------------------
  // Search Indexer (ACF-aware site search)
  // Builds a normalized text blob per post (title + content + every ACF text
  // field, including each flexible_content layout) into postmeta. Exposes
  // an admin page under Terra, AJAX backfill, and `wp terra-search` CLI.
  // The REST endpoint is registered in endpoint_config.php (POST /tf_api/search).
  // ---------------------------------------------------------------------------
  'search_indexer' => [
    'post_types' => ['post', 'page', 'media-and-press', 'trabajo'],
    'capability' => 'manage_options',
  ],

  // ---------------------------------------------------------------------------
  // Content Types
  // Custom post types and taxonomies registered via Terra classes
  // ---------------------------------------------------------------------------
  'post_types'  => require __DIR__ . '/post-types_config.php',    // Custom Post Types (team, services, etc.)
  'taxonomies'  => require __DIR__ . '/taxonomy_config.php',      // Custom Taxonomies (categories for CPTs)

  // ---------------------------------------------------------------------------
  // Post Type Fields
  // ACF field groups attached to specific post types
  // ---------------------------------------------------------------------------
  'post_type_fields' => require __DIR__ . '/post-type-fields_config.php',

  // ---------------------------------------------------------------------------
  // API & AJAX
  // REST endpoints and AJAX handlers with auto nonce verification
  // ---------------------------------------------------------------------------
  'endpoint' => require __DIR__ . '/endpoint_config.php',         // Custom REST API endpoints
  'ajax'     => require __DIR__ . '/ajax_config.php',             // AJAX handlers (with sanitization + nonce)

  // ---------------------------------------------------------------------------
  // Admin Interface
  // Control what editors see, custom admin pages, and admin behaviors
  // ---------------------------------------------------------------------------
  'admin_controller' => require __DIR__ . '/admin-controller_config.php',  // Hide metaboxes, redirects per template/post_type
  'admin_pages'      => require __DIR__ . '/admin-pages_config.php',       // Custom pages under Terra menu (HTML templates)

  // ---------------------------------------------------------------------------
  // WYSIWYG Toolbars
  // Custom TinyMCE toolbar presets for ACF WYSIWYG fields per module
  // ---------------------------------------------------------------------------
  'wysiwyg_toolbars' => require __DIR__ . '/wysiwyg-toolbars_config.php',

  // ---------------------------------------------------------------------------
  // Blocks
  // ACF Gutenberg blocks (default framework blocks + project-specific)
  // ---------------------------------------------------------------------------
  'default_blocks' => require __DIR__ . '/default-blocks_config.php',   // Framework default blocks
  'custom_blocks'  => require __DIR__ . '/custom-blocks_config.php',    // Project-specific blocks

  // ---------------------------------------------------------------------------
  // Custom ACF Field Types
  // Register custom field types with their UI file and config options
  // ---------------------------------------------------------------------------
  'custom_acf' => [
    'spacing' => [
      'file'    => 'functions/project/utilities/acf/acf-spacing/init.php',
      'options' => [
        'large'  => ['top' => 'u--pt-10 u--pt-tablets-15', 'bottom' => 'u--pb-10 u--pb-tablets-15'],
        'medium' => ['top' => 'u--pt-7 u--pt-tablets-10',  'bottom' => 'u--pb-7 u--pb-tablets-10'],
        'small'  => ['top' => 'u--pt-5 u--pt-tablets-7',   'bottom' => 'u--pb-5 u--pb-tablets-7'],
      ],
    ],
    'bg_color' => [
      'file'    => 'functions/project/utilities/acf/acf-bg-color/init.php',
      'options' => [
        'default' => [
          'transparent' => ['label' => 'Transparent', 'color' => '#ffffff00'],
          'white'      => ['label' => 'White',      'color' => '#ffffff'],
          'light-blue'  => ['label' => 'Light blue',  'color' => '#F7FBFF'],
        ],
        'reduced' => [
          'white'      => ['label' => 'White',      'color' => '#ffffff'],
          'light-blue'  => ['label' => 'Light blue',  'color' => '#F7FBFF'],
        ],
        'white-transparent' => [
          'transparent' => ['label' => 'Transparent', 'color' => '#ffffff00'],
          'white'       => ['label' => 'White',       'color' => '#ffffff'],
        ]
      ],
    ],
    'page_modules' => [
      'file' => 'functions/framework/includes/acf-page-modules/init.php',
    ],
  ],

  // ---------------------------------------------------------------------------
  // Flexible Content (Island Pattern)
  // Modules and heroes for page-modules.php template
  // Each has its own folder with one file per layout
  // ---------------------------------------------------------------------------
  'flexible_modules'      => require __DIR__ . '/flexible-modules/index.php',   // Page modules (accordion, CTA, etc.)
  'flexible_heros'        => require __DIR__ . '/flexible-heros/index.php',     // Hero sections (max 1 per page)
  'flexible_restrictions' => require __DIR__ . '/flexible-restrictions_config.php', // Restrict layouts per post/type/template

  // ---------------------------------------------------------------------------
  // Options Pages (Island Pattern)
  // ACF options pages with editable fields (get_field('key', 'option'))
  // Each has its own folder with one file per field group
  // ---------------------------------------------------------------------------
  'general_options' => require __DIR__ . '/general-options/index.php',     // General Options (logo, social, footer)

  // ---------------------------------------------------------------------------
  // Deploy & Build
  // File paths for Vite build, hash cache busting, and asset loading
  // ---------------------------------------------------------------------------
  'local_variable' => 'functions/project/deploy/local-variable.php',       // Dev/prod environment variables
  'hash'           => 'functions/project/deploy/hash.php',                 // Build hash for cache busting
  'enqueues'       => 'functions/project/deploy/enqueues.php',             // Script/style registration

  // ---------------------------------------------------------------------------
  // Redirects
  // Pages and taxonomies to redirect (used by Redirect_Stage_Urls class)
  // ---------------------------------------------------------------------------
  'redirect_pages'        => ['34434', '45'],
  'redirect_single_pages' => ['1'],
  'redirect_tax_pages'    => ['insight-type', 'category'],

  // ---------------------------------------------------------------------------
  // Grammar Checker
  // Auto-checks spelling/grammar on save (requires Module_Manager active)
  // ---------------------------------------------------------------------------
  'grammar' => [
    'post_types'     => ['trabajo', 'page'],
    'trigger_username' => 'terradev',
  ],

  // ---------------------------------------------------------------------------
  // Mail Notifications (disabled)
  // Uncomment to enable email notifications via Mail_To class
  // ---------------------------------------------------------------------------
  // 'mail_to_config' => [
  //   'email'   => 'nerea@terrahq.com',
  //   'subject' => 'test',
  //   'message' => 'test',
  // ],

];

