<?php
/**
 * Class WP_Functionality
 *
 * Extends WordPress with additional functionality and utility methods.
 * Handles MIME types, admin UI tweaks, and query modifications.
 *
 * Features:
 * - Allows SVG, WebP, and JSON file uploads
 * - Removes admin bar bump CSS
 * - Adds custom post states (e.g., "Home" label for home page template)
 * - Excludes password-protected posts from queries by default
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @example
 * // Instantiate in theme setup (usually called by Default_Setup)
 * new WP_Functionality();
 *
 * // Available utility methods:
 * $wp_func = new WP_Functionality();
 * $target = $wp_func->get_target_link(true, 'External Link');
 * // Returns: target='_blank' rel='noopener noreferrer' aria-label="External Link, opens a new window"
 */
class WP_Functionality {
  public function __construct() {
    $this->init();
  }

  protected function init() {
    add_action('after_setup_theme', [$this, 'theme_support']);
    add_filter('upload_mimes', [$this, 'cc_mime_types']);
    add_action('get_header', [$this, 'remove_admin_login_header']);
    add_filter('display_post_states', [$this, 'wpsites_custom_post_states']);
    add_action('pre_get_posts', [$this, 'custom_set_default_has_password']);
  }

  /**
   * Register theme support features.
   */
  public function theme_support() {
    add_theme_support('post-thumbnails');
  }

  /**
   * Get the page ID by its title.
   */
  public function get_page_id_by_title($title) {
    $page = get_page_by_title($title);
    return $page->ID;
  }

  /**
   * Allow upload of additional MIME types.
   */
  public function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    $mimes['json'] = 'text/plain';
    return $mimes;
  }

  /**
   * Get the target attribute for a link.
   */
  public function get_target_link($target, $text) {
    $targetType = ($target) ? '_blank' : "_self";
    $targetURL = "target='" . $targetType . "'";
    $targetURL .= ($target) ? " rel='noopener noreferrer'" : '';
    $targetURL .= ($target) ? 'aria-label="' . $text . ', opens a new window"' : '';
    return $targetURL;
  }

  /**
   * Remove HTML margin top on the WordPress dashboard.
   */
  public function remove_admin_login_header() {
    remove_action('wp_head', '_admin_bar_bump_cb');
  }

  /**
   * Callback function to modify the display of post states on the Edit Post/Page screen.
   */
  public function wpsites_custom_post_states($states) {
    global $post;

    // Check if the global $post variable is set
    if ($post) {
      // Check if the post type is 'page' and the page template is 'page-home.php'
      if (('page' == get_post_type($post->ID)) && ('page-home.php' == get_page_template_slug($post->ID))) {
        // If the conditions are met, add a custom state label 'Home'
        $states[] = __('Home');
      }
    }

    // Return the modified array of post states
    return $states;
  }

  /**
   * Modifies WordPress default queries to ensure the 'has_password' 
   * parameter is set to 'false' if it is not already defined.
   */
  public function custom_set_default_has_password($query) {
    // Check if the 'has_password' parameter is not defined
    if (!isset($query->query_vars['has_password'])) {
      // Set 'has_password' to false by default
      $query->set('has_password', false);
    }
  }
}
