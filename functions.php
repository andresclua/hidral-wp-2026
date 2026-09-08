<?php

if (!defined('THEME_PATH')) {
  define('THEME_PATH', get_template_directory());
}

require THEME_PATH . '/functions/framework/classes/index.php';
require THEME_PATH . '/functions/project/utilities/index.php';


class Core {

  protected array $projectConfig = [];
  protected array $recipients = [];
  protected array $flexible = [];

  public function __construct() {

    $this->projectConfig = require THEME_PATH . '/functions/project/config/index.php';
    $this->recipients = get_recipient_emails();

  }
  public function init(): void {
    $this->default();
    $this->project();
  }

  protected function default(): void {

    new Default_Setup([
      'image_sizes' => $this->projectConfig['image_sizes'] ?? [],
      'image_type'  =>  $this->projectConfig['image_type'] ?? [],
      'enable_search_modules' => $this->projectConfig['enable_search_modules'] ?? [],
      'enable_search_gutenberg' => $this->projectConfig['enable_search_gutenberg'] ?? true,
      'enable_vulnerability' => $this->projectConfig['enable_vulnerability'] ?? [],
    ]);

    new Default_Files([
      'local_variable' => $this->projectConfig['local_variable'] ?? '',
      'hash' => $this->projectConfig['hash'] ?? '',
      'enqueues' => $this->projectConfig['enqueues'] ?? '',
    ]);

    // ACF-aware search index (postmeta blob + admin UI + REST + CLI).
    if (!empty($this->projectConfig['search_indexer'])) {
      new Search_Indexer((object) $this->projectConfig['search_indexer']);
    }

    new System_Warning([
      'recipients' => $this->recipients,
      'google_search_console_enabled' => true,
      'mail_to_enabled' => true,
      'mail_to_config' => $this->projectConfig['mail_to_config'] ?? [],
      'url_health_checked_enabled' => true,
      'grammar_config' => $this->projectConfig['grammar'] ?? [],
    ]);

    // custom default blocks
    new Default_Blocks([
      'default_blocks' => $this->projectConfig['default_blocks'] ?? [],
      'template_dir' =>  get_stylesheet_directory() . '/functions/framework/blocks'
    ]);

    // WYSIWYG custom toolbars
    new WYSIWYG_Toolbars($this->projectConfig['wysiwyg_toolbars'] ?? []);

    // Custom ACF field types (file + options from project)
    foreach (($this->projectConfig['custom_acf'] ?? []) as $key => $acf_field) {
      ACF_Builder::set_config($key, $acf_field['options'] ?? []);
      require_once THEME_PATH . '/' . $acf_field['file'];
    }

    // Flexible Content (island pattern)
    if (!empty($this->projectConfig['flexible_modules'])) {
      $this->flexible[] = new Flexible_Content($this->projectConfig['flexible_modules']);
    }
    if (!empty($this->projectConfig['flexible_heros'])) {
      $this->flexible[] = new Flexible_Content($this->projectConfig['flexible_heros']);
    }

    // Post Type Fields (simple ACF field groups)
    if (!empty($this->projectConfig['post_type_fields'])) {
      new Post_Type_Fields($this->projectConfig['post_type_fields']);
    }

    // Flexible Content layout restrictions per post/type/template
    if (!empty($this->projectConfig['flexible_restrictions'])) {
      new Flexible_Restrictions($this->projectConfig['flexible_restrictions']);
    }

    // Options Pages (island pattern)
    if (!empty($this->projectConfig['general_options'])) {
      new Options_Page($this->projectConfig['general_options']);
    }

  }

  protected function project(): void {

    // Register Post Types
    foreach (($this->projectConfig['post_types'] ?? []) as $pt) {
      new Custom_Post_Type((object) [
        'post_type' => $pt['post_type'],
        'singular_name' => $pt['singular_name'],
        'plural_name' => $pt['plural_name'],
        'args' => $pt['args'] ?? [],
      ]);
    }

   foreach (($this->projectConfig['taxonomies'] ?? []) as $tx) {
      new Custom_Taxonomy((object) [
        'taxonomy'       => $tx['taxonomy'],
        'object_type'    => $tx['object_type'],
        'singular_name'  => $tx['singular_name'],
        'plural_name'    => $tx['plural_name'],
        'args'           => $tx['args'] ?? [],
      ]);
    }


   // custom blocks
    new Custom_Blocks([
      'custom_blocks' => $this->projectConfig['custom_blocks'] ?? [],
      'template_dir' =>  get_stylesheet_directory() . '/functions/project/blocks'
    ]);

    if (Module_Manager::is_active('redirects')) {
      new Redirect_Stage_Urls([
        'pages' => $this->projectConfig['redirect_pages'] ?? [],
        'single' => $this->projectConfig['redirect_single_pages'] ?? [],
        'taxonomies' => $this->projectConfig['redirect_tax_pages'] ?? [],
      ]);
    }

    foreach (($this->projectConfig['endpoint'] ?? []) as $endpoint) {
      new Custom_API_Endpoint((object) $endpoint);
    }

    foreach (($this->projectConfig['ajax'] ?? []) as $ajax) {
      new AJAX_Request((object) $ajax);
    }

    foreach (($this->projectConfig['admin_controller'] ?? []) as $admin_ctrl) {
      new Admin_Controller((object) $admin_ctrl);
    }

    foreach (($this->projectConfig['admin_pages'] ?? []) as $page) {
      new Admin_Page((object) $page);
    }

    // Admin palette — branded WP-Admin colour scheme + live re-skin settings page.
    // Engine lives in the framework; the theme provides the brand colours (runtime
    // source; SCSS fallbacks in _vars.scss are safety only). The stylesheet handle
    // defaults to 'admin-backend-style' inside the class — pass 'style_handle' only
    // if this theme enqueues its admin CSS under a different name.
    if (Module_Manager::is_active('admin_palette')) {
      new Admin_Palette([
        'brand_colors' => [
          'bg_a'        => '#0f0f0f',
          'bg_b'        => '#1f1f1f',
          'highlight_a' => '#f96e43',
          'highlight_b' => '#fefefe',
          'text'        => '#fefefe',
        ],
      ]);
    }

    new Call_Cronjob((object) array(
      'cronName' => 'every_thirty_minutes',
      'interval' =>  1800,
      'functionName' => 'detect_robot_callback',
    ));

    // Pass grammar config to framework via global (Grammar class + admin pages loaded in classes/index.php)
    $GLOBALS['terra_grammar_config'] = $this->projectConfig['grammar'] ?? [];

  }
}


$app = new Core();
$app->init();

// ============================================================================
// PAGES: Remove SEO column, add Template column
// ============================================================================

add_filter('manage_pages_columns', function ($columns) {
    unset($columns['wpseo-score']);
    unset($columns['wpseo-score-readability']);
    unset($columns['wpseo-title']);
    unset($columns['wpseo-metadesc']);
    unset($columns['wpseo-focuskw']);
    unset($columns['wpseo-links']);
    unset($columns['wpseo-linked']);

    $columns['page_template'] = 'Template';
    return $columns;
});

add_action('manage_pages_custom_column', function ($column, $post_id) {
    if ($column === 'page_template') {
        $template = get_post_meta($post_id, '_wp_page_template', true);
        echo $template && $template !== 'default' ? esc_html($template) : '—';
    }
}, 10, 2);
