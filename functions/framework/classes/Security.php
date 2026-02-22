<?php
/**
 * Class Security
 *
 * Handles WordPress security hardening by removing unnecessary features,
 * hiding sensitive information, and cleaning up the wp_head output.
 *
 * Features:
 * - Removes unnecessary user roles (wpseo_manager, wpseo_editor, subscriber, author, contributor)
 * - Hides WordPress version from login errors
 * - Removes WordPress generator meta tag
 * - Cleans up wp_head (removes RSD, WLW, emojis, feed links, etc.)
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @example
 * // Instantiate in theme setup (usually called by Default_Setup)
 * new Security();
 */
class Security {
  public function __construct() {
    $this->init();
  }

  protected function init() {
    add_action('init', [$this, 'terra_setup']);
    add_filter('login_errors', [$this, 'show_less_login_info']);
    add_filter('the_generator', [$this, 'no_generator']);
    
    $this->remove_wp_head_actions();
  }

  /**
   * Theme setup function to add theme support, custom image sizes, and remove roles.
   * @return void
   */
  public function terra_setup() {
    // Remove unwanted user roles
    remove_role('wpseo_manager');
    remove_role('wpseo_editor');
    remove_role('subscriber');
    remove_role('author');
    remove_role('contributor');
  }

  /**
   * Modify login error messages for security purposes.
   * 
   * @return string Custom error message to display on failed login attempts.
   */
  public function show_less_login_info() {
    return "<strong>ERROR</strong>: Stop guessing!";
  }

  /**
   * Disable WordPress version generation for security.
   * 
   * @return string An empty string to remove WordPress version output.
   */
  public function no_generator() {
    return '';
  }

  protected function remove_wp_head_actions() {
    // Remove the WordPress version number for security reasons
    remove_action('wp_head', 'wp_generator');

    // Remove the Really Simple Discovery (RSD) link
    remove_action('wp_head', 'rsd_link');

    // Remove the Windows Live Writer (WLW) manifest link
    remove_action('wp_head', 'wlwmanifest_link');

    // Remove the index link
    remove_action('wp_head', 'index_rel_link');

    // Remove the default feed links
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);

    // Remove the link to the previous and next posts
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

    // Remove the WordPress shortlink for the current page
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

    // Disable the emoji detection script
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
  }
}
