<?php
/**
 * Class Clean_Wp
 *
 * Optimizes WordPress by removing unnecessary scripts, styles, and features.
 * Also adds useful functionality like async/defer script loading and
 * global JavaScript variables.
 *
 * Features:
 * - Removes jQuery migrate, wp-embed, and block library styles
 * - Adds async/defer attributes to scripts based on handle name
 * - Exposes global JS variables (ajax_url, current_page_ID, root_url, theme_url)
 * - Removes query strings from static resources
 * - Lowers Yoast SEO metabox priority
 * - Customizes TinyMCE heading options
 * - Creates ACF General Options page
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @example
 * // Instantiate in theme setup (usually called by Default_Setup)
 * new Clean_Wp();
 */
class Clean_Wp {
  public function __construct() {
    $this->init();
  }

  protected function init() {
    add_action('wp_enqueue_scripts', [$this, 'disable_default_styles_and_scripts'], 100);
    add_filter('script_loader_tag', [$this, 'add_async_defer_attr'], 10, 2);
    add_action('wp_head', [$this, 'variables_in_header']);
    add_filter('wpseo_canonical', [$this, 'prefix_filter_canonical_example'], 20);
    add_filter('wpseo_metabox_prio', [$this, 'lower_wpseo_priority']);
    add_filter('tiny_mce_before_init', [$this, 'remove_headings_from_editor']);
    add_action('init', [$this, 'flush_rewritte']);
    // General Options page now registered via Options_Page class in project config
    // add_action('admin_menu', [$this, 'create_general_opts_panel'], 6);
    
    if (!is_admin()) {
      add_filter('script_loader_src', [$this, '_remove_query_strings_1'], 15, 1);
      add_filter('style_loader_src', [$this, '_remove_query_strings_1'], 15, 1);
      add_filter('script_loader_src', [$this, '_remove_query_strings_2'], 15, 1);
      add_filter('style_loader_src', [$this, '_remove_query_strings_2'], 15, 1);
    }

    add_action('admin_menu', [$this, 'remove_posts_menu']);
    add_action('admin_menu', [$this, 'remove_comments_menu']);
    add_action('init', [$this, 'disable_comments_support']);
    add_action('after_setup_theme', [$this, 'delete_hello_world_post']);
    add_action('wp_dashboard_setup', [$this, 'customize_dashboard']);
  }

  /**
   * Disable default WordPress scripts and styles for optimization.
   */
  public function disable_default_styles_and_scripts() {
    if (is_admin()) {
      return;
    }

    global $wp_scripts;

    if (!empty($wp_scripts->registered['jquery'])) {
      $wp_scripts->registered['jquery']->deps = array_diff(
        $wp_scripts->registered['jquery']->deps,
        ['jquery-migrate']
      );
    }

    wp_deregister_script('jquery');
    // wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js', [], null, true);
    wp_deregister_script('wp-embed');
    wp_deregister_script('jquery-migrate');
    wp_deregister_style('wp-block-library');
    wp_deregister_style('global-styles');
    wp_deregister_style('classic-theme-styles');
    wp_deregister_style('wp-block-library-theme');
  }

  /**
   * Add async or defer attributes to enqueued scripts.
   */
  public function add_async_defer_attr($tag, $handle) {
    if (strpos($handle, "async")) {
      $tag = str_replace(' src', ' async src', $tag);
    }

    if (strpos($handle, "defer")) {
      $tag = str_replace(' src', ' defer src', $tag);
    }

    return $tag;
  }

  /**
   * Add JavaScript variables to the WordPress head section.
   */
  public function variables_in_header() {
    ?>
    <script>
      var base_wp_api = <?php echo json_encode(
        array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'current_page_ID' => get_the_ID(),
          'current_url' => get_permalink(),
          'root_url' => get_site_url(),
          'theme_url' => get_template_directory_uri(),
          'nonces' => array(
            'loadmore_posts' => wp_create_nonce('terra_ajax_loadmore_posts'),
          ),
        )
      ); ?>
    </script>
    <?php
  }

  /**
   * Custom WordPress filter function for generating canonical URLs.
   */
  public function prefix_filter_canonical_example($canonical) {
    // Check if the current request is using HTTPS
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    // Construct the canonical URL using the protocol, domain, and request URI
    $canonical = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Return the modified canonical URL
    return $canonical;
  }

  /**
   * Sets Yoast in a page right under ACF as it gives Yoast low priority
   */
  public function lower_wpseo_priority($html) {
    return 'low';
  }

  /**
   * Add/remove custom Headings for Editor
   */
  public function remove_headings_from_editor($settings) {
    $settings['block_formats'] = 'Paragraph=p; Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Preformatted=pre;';
    return $settings;
  }

  /**
   * Flush rewrite rules to ensure updated permalinks.
   */
  public function flush_rewritte() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules(false);
  }

  /**
   * Remove Query Strings From Static Resources
   */
  public function _remove_query_strings_1($src) {
    $rqs = explode('?ver', $src);
    return $rqs[0];
  }

  /**
   * Remove Query Strings From Static Resources
   */
  public function _remove_query_strings_2($src) {
    $rqs = explode('&ver', $src);
    return $rqs[0];
  }

   /**
   * Remove Query Strings From Static Resources
   */
  /**
   * Hide the default "Posts" menu from wp-admin.
   */
  public function remove_posts_menu() {
    remove_menu_page('edit.php');
  }

  /**
   * Hide the "Comments" menu from wp-admin.
   */
  public function remove_comments_menu() {
    remove_menu_page('edit-comments.php');
  }

  /**
   * Disable comments support on all post types and close existing comments.
   */
  public function disable_comments_support() {
    foreach (get_post_types() as $post_type) {
      if (post_type_supports($post_type, 'comments')) {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
      }
    }
  }

  /**
   * Delete the default "Hello World" post on theme activation.
   * Runs once and stores a flag in wp_options to avoid repeat queries.
   */
  public function delete_hello_world_post() {
    if (get_option('terra_hello_world_deleted')) {
      return;
    }

    $hello = get_page_by_path('hello-world', OBJECT, 'post');
    if ($hello) {
      wp_delete_post($hello->ID, true);
    }

    update_option('terra_hello_world_deleted', true);
  }

  /**
   * Remove default dashboard widgets and register custom ones from project config.
   */
  public function customize_dashboard() {
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    remove_action('welcome_panel', 'wp_welcome_panel');

    $config_path = get_template_directory() . '/functions/project/config/dashboard_config.php';
    if (!file_exists($config_path)) return;

    $config = include $config_path;
    $cards  = isset($config['cards']) ? $config['cards'] : array();

    foreach ($cards as $i => $card) {
      $context = isset($card['column']) ? $card['column'] : ($i === 0 ? 'normal' : 'side');
      wp_add_dashboard_widget(
        'terra_dashboard_' . $i,
        $card['title'],
        function () use ($card) {
          $template = get_template_directory() . '/functions/project/admin-pages/dashboard.php';
          if (file_exists($template)) {
            include $template;
          }
        },
        null,
        null,
        $context
      );
    }
  }

  public function create_general_opts_panel($src) {
    // Register admin page options
    
    if (function_exists('acf_add_options_page')) {

        acf_add_options_page(array(
            'page_title'     => 'General Options',
            'menu_title'    => 'General Options',
            'menu_slug'     => 'general-options',
            'capability'    => 'edit_posts',
            'parent_slug'   => 'terra_dashboard',
            'redirect'        => false
        ));
    }
  }
}
