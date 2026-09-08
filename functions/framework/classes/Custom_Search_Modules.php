<?php
/**
 * Class Custom_Search_Modules
 *
 * Creates an admin menu page for managing search modules.
 * Provides a UI to configure and monitor site search functionality.
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param object $config Configuration object (currently unused, for future expansion)
 *
 * @example
 * // Usually instantiated by Default_Setup
 * new Custom_Search_Modules((object) []);
 */

global $custom_module;

require get_template_directory() . '/functions/framework/includes/search_modules/search_modules.php';

class Custom_Search_Modules {

    /**
     * Constructor for Custom_Search_Modules.
     *
     * @param object $config Configuration object for future expansion.
     */
    /** @var string */
    private $hook_suffix = '';

    public function __construct($config) {
        global $custom_module;
        $custom_module = $this;
        add_action('admin_menu', array($this, 'create_search_module_page'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles($hook) {
        if ($hook !== $this->hook_suffix) {
            return;
        }
        wp_enqueue_style('search-module-style', get_template_directory_uri() . '/functions/framework/includes/search_modules/style.css');
    }

    public function create_search_module_page() {
        $this->hook_suffix = add_submenu_page(
            'terra_dashboard',
            'Search Modules',
            'Search Modules',
            'manage_options',
            'custom_search_module',
            'show_custom_search_module'
        );
    }
}
?>
