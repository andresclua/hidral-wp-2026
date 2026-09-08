<?php
/**
 * Class Default_Setup
 *
 * Main theme initialization class that bootstraps all framework components.
 * This is the central orchestrator that initializes security, optimization,
 * images, functionality, and monitoring features.
 *
 * Components initialized:
 * - Security: Security hardening and cleanup
 * - Clean_Wp: WordPress optimization
 * - Images: Image handling and responsive images
 * - WP_Functionality: Additional WP features
 * - Custom_Search_Modules: Search admin panel (optional)
 * - Custom_Search_Gutenberg: Gutenberg blocks usage panel (optional)
 * - Custom_Search_Forms: Contact Form 7 usage panel (optional, requires CF7)
 * - WP_Vulnerability_Checker: Advanced security rules (optional)
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param array $config Configuration options
 * @param array $config['image_sizes']           Custom image sizes to register
 * @param array $config['image_type']            Enabled image functions
 * @param bool  $config['enable_search_modules'] Enable search modules admin (default: true)
 * @param bool  $config['enable_search_gutenberg'] Enable Gutenberg usage admin (default: true)
 * @param bool  $config['enable_search_forms']   Enable Contact Form 7 usage admin (default: true)
 * @param bool  $config['enable_vulnerability']  Enable vulnerability checker (default: true)
 *
 * @example
 * new Default_Setup([
 *     'image_sizes' => [
 *         ['name' => 'tablets', 'w' => 810, 'h' => 9999, 'crop' => false],
 *         ['name' => 'mobile', 'w' => 580, 'h' => 9999, 'crop' => false],
 *     ],
 *     'image_type' => ['generate_image_tag', 'wp_render_image'],
 *     'enable_search_modules' => true,
 *     'enable_vulnerability' => true,
 * ]);
 */
class Default_Setup {
  protected $config = [
    'image_sizes' => [],
    'image_type' => [],
    'enable_search_modules' => true,
    'enable_search_gutenberg' => true,
    'enable_search_forms' => true,
    'enable_vulnerability' => true
  ];

  public function __construct($config = []) {
    $this->config = array_merge($this->config, $config);
    $this->init();
  }

  protected function init() {

    if (Module_Manager::is_active('security')) {
      new Security();
    }

    if (Module_Manager::is_active('clean_wp')) {
      new Clean_Wp();
    }

    if (Module_Manager::is_active('images')) {
      new Images($this->config['image_sizes'], $this->config['image_type']);
    }

    if (Module_Manager::is_active('wp_functionality')) {
      new WP_Functionality();
    }

    if ($this->config['enable_search_modules'] && Module_Manager::is_active('search_modules')) {
      new Custom_Search_Modules((object) array());
    }

    if ($this->config['enable_search_gutenberg'] && Module_Manager::is_active('search_gutenberg')) {
      new Custom_Search_Gutenberg((object) array());
    }

    // Contact Form 7 usage report. The class itself bails out when CF7 is not
    // installed, so projects without the plugin simply never see the page.
    if ($this->config['enable_search_forms'] && Module_Manager::is_active('search_forms')) {
      new Custom_Search_Forms((object) array());
    }

    if ($this->config['enable_vulnerability'] && Module_Manager::is_active('vulnerability_checker')) {
      $is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8888'])
        || strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false
        || strpos($_SERVER['HTTP_HOST'] ?? '', '.test') !== false;

      if (class_exists('WP_Vulnerability_Checker') && !$is_local) {
        new WP_Vulnerability_Checker([
          'restrict_users_endpoint'     => true,
          'enforce_strong_passwords'    => false,
          'cors_protect_rest_api'       => true,
          'remove_wp_version_headers'   => true,
          'generic_rest_errors'         => true,
          'redirect_author_archives'    => true,
          'shorten_password_reset_expiry' => true,
        ]);
      }
    }
  }

}
