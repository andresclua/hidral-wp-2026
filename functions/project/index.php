<?php

if (!defined('THEME_PATH')) {
  define('THEME_PATH', get_template_directory());
}

require THEME_PATH . '/functions/framework/classes/index.php';


class Core {

  protected array $projectConfig = [];
  protected array $recipients = [];

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
      'enable_vulnerability' => $this->projectConfig['enable_vulnerability'] ?? [],
    ]);

    new Default_Files([
      'local_variable' => $this->projectConfig['local_variable'] ?? '',
      'hash' => $this->projectConfig['hash'] ?? '',
      'enqueues' => $this->projectConfig['enqueues'] ?? '',
    ]);

    // new System_Warning([
    //   'recipients' => $this->recipients,
    //   'lighthouse_enabled' => true,
    //   'lighthouse_url' => $this->projectConfig['lighthouse_url'] ?? '',
    //   'google_search_console_enabled' => true ,
    //   'mail_to_enabled' => true,
    //   'mail_to_config' => $this->projectConfig['mail_to_config'] ?? [],
    //   'url_health_checked_enabled' => true
    // ]);

    // custom default blocks
    new Default_Blocks([
      'default_blocks' => $this->projectConfig['default_blocks'] ?? [],
      'template_dir' =>  get_stylesheet_directory() . '/functions/framework/blocks'
    ]);

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
      'template_dir' =>  get_stylesheet_directory() . '/functions/framework/blocks'
    ]);
      
    new Redirect_Stage_Urls([
      'pages' => $this->projectConfig['redirect_pages'] ?? [],
      'single' => $this->projectConfig['redirect_single_pages'] ?? [],
      'taxonomies' => $this->projectConfig['redirect_tax_pages'] ?? [],
    ]);
    
    foreach (($this->projectConfig['endpoint'] ?? []) as $endpoint) {
      new Custom_API_Endpoint((object) $endpoint);
    }

    foreach (($this->projectConfig['ajax'] ?? []) as $ajax) {
      new AJAX_Request((object) $ajax);
    }

    foreach (($this->projectConfig['admin_controller'] ?? []) as $admin_ctrl) {
      new Admin_Controller((object) $admin_ctrl);
    }

    new Call_Cronjob((object) array(
      'cronName' => 'every_thirty_minutes',
      'interval' =>  1800,
      'functionName' => 'detect_robot_callback',
    ));

    new Grammar($this->projectConfig['grammar'] ?? []);

  }
}

$app = new Core();
$app->init();
