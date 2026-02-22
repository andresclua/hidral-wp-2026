<?php
/**
 * Class Google_Search_Console
 *
 * Integrates Google Search Console data into the WordPress admin.
 * Adds a submenu page under System Warning to view GSC reports.
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param array $config Configuration options (for future expansion)
 *
 * @example
 * // Usually instantiated by System_Warning when google_search_console_enabled is true
 * new Google_Search_Console([]);
 */

require get_template_directory() . '/functions/framework/includes/google_search_console/gsc.php';

class Google_Search_Console {

    /**
     * Constructor for Google_Search_Console.
     *
     * @param array $config Configuration options.
     */
    public function __construct($config) {
        add_action('admin_menu', [$this, 'register_menu'], 99);
    }

    /**
     * Registers the GSC submenu page under System Warning.
     *
     * @return void
     */
    public function register_menu() {
        add_submenu_page(
            'system_warning',
            'GSC',
            'GSC',
            'manage_options',
            'custom_gsc_settings',
            'terra_gsc_admin_page'
        );
    }

}
?>
