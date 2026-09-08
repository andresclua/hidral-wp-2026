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
    add_filter('wp_check_filetype_and_ext', [$this, 'cc_check_filetype'], 10, 4);
    add_action('get_header', [$this, 'remove_admin_login_header']);
    add_filter('display_post_states', [$this, 'wpsites_custom_post_states']);
    add_action('pre_get_posts', [$this, 'custom_set_default_has_password']);
    add_filter('the_password_form', [$this, 'custom_password_form']);
    add_action('template_redirect', [$this, 'password_protected_full_page']);
  }

  /**
   * Register theme support features.
   */
  public function theme_support() {
    add_theme_support('post-thumbnails');
    add_post_type_support('page', 'excerpt');
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
    $mimes['json'] = 'application/json';
    return $mimes;
  }

  /**
   * Bypass WP's real-mime check for extensions where finfo and the registered
   * MIME may disagree (e.g. .json files detected as text/plain).
   */
  public function cc_check_filetype($data, $file, $filename, $mimes) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ($ext === 'json') {
      $data['ext']  = 'json';
      $data['type'] = 'application/json';
    }

    if ($ext === 'svg') {
      $data['ext']  = 'svg';
      $data['type'] = 'image/svg+xml';
    }

    return $data;
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
    // Skip admin, singular pages, and queries that already set has_password
    if (is_admin() || $query->is_singular() || isset($query->query_vars['has_password'])) {
      return;
    }
    $query->set('has_password', false);
  }

  /**
   * Custom password-protected form.
   */
  public function custom_password_form() {
    // Allow project override via components/form/password-form.php
    $override = locate_template('components/form/password-form-a.php');
    if ($override) {
      ob_start();
      include $override;
      return ob_get_clean();
    }

    // Default framework form
    $action = esc_url(site_url('wp-login.php?action=postpass', 'login_post'));
    $redirect = esc_url(get_permalink());

    return '
    <div class="c--password-form">
      <div class="c--password-form__wrapper">
        <svg class="c--password-form__icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <h2 class="c--password-form__title">This content is protected</h2>
        <p class="c--password-form__subtitle">Please enter the password to view this page.</p>
        <form action="' . $action . '" method="post" class="c--password-form__form">
          <input type="hidden" name="post_password" value="" />
          <div class="c--password-form__field">
            <input name="post_password" type="password" placeholder="Enter password" class="c--password-field__input" required />
          </div>
          <input type="hidden" name="_wp_http_referer" value="' . $redirect . '" />
          <button type="submit" class="c--password-form__btn">Submit</button>
        </form>
      </div>
    </div>';
  }

  /**
   * Override the entire page template for password-protected pages.
   * Shows only header + password form + footer, hiding all page content.
   */
  public function password_protected_full_page() {
    if (!is_singular() || !post_password_required()) {
      return;
    }

    get_header();
    echo '<main>' . get_the_password_form() . '</main>';
    get_footer();
    exit;
  }
}
