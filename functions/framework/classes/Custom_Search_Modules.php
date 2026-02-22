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
    public function __construct($config) {
        global $custom_module;
        $custom_module = $this; // Assign the instance to the global variable
        add_action('init', array($this, 'add_search_module_table'));
    }

    /**
     * Adds the Lighthouse report menu and starts the cron job.
     *
     * This method registers the admin menu page and starts the cron job to fetch the Lighthouse reports.
     *
     * @return void
     */
    public function add_search_module_table() {
        if(is_user_logged_in() && is_admin()){
            wp_enqueue_style('search-module-style', get_template_directory_uri() . '/functions/framework/includes/search_modules/style.css');
            $this->create_search_module_page(); // Create an admin menu page for the report
        } 
    }

    /**
     * Adds a Lighthouse report page to the WordPress admin panel.
     *
     * @return void
     */
    public function create_search_module_page(){
        // Use WordPress's add_menu_page to create a custom admin page
        add_menu_page(
            'Search modules', // Page title in the admin panel
            'Search modules', // Text for the menu item
            'manage_options',    // Capability required to view the menu
            'custom_search_module', // Slug for the menu page
            'show_custom_search_module',        // Function to display the content of the page (lighthouse/showTable.php)
            'dashicons-search', // Dashicon icon for the menu item
            101                  // Position in the menu
        );
    }
}
?>
