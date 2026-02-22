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
 * - WP_Vulnerability_Checker: Advanced security rules (optional)
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param array $config Configuration options
 * @param array $config['image_sizes']           Custom image sizes to register
 * @param array $config['image_type']            Enabled image functions
 * @param bool  $config['enable_search_modules'] Enable search modules admin (default: true)
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
    'enable_vulnerability' => true
  ];

  public function __construct($config = []) {
    $this->config = array_merge($this->config, $config);
    $this->init();
  }

  protected function init() {

    new Security();

    new Clean_Wp();

    new Images($this->config['image_sizes'], $this->config['image_type']);

    new WP_Functionality();

    if($this->config['enable_search_modules']){
      new Custom_Search_Modules((object) array());
    }
    if($this->config['enable_vulnerability']){
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
