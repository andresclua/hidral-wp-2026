<?php
/**
 * Class Terra_URL_Health_Check
 *
 * Monitors site URLs for health issues (404s, 500s, timeouts).
 * Runs periodic checks via WP Cron and reports issues via email.
 * Adds an admin submenu under System Warning for viewing reports.
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param object $config Configuration object
 * @param int    $config->interval Cron interval in seconds
 * @param string $config->email    Email recipient for alerts
 * @param string $config->url      Base URL to check
 *
 * @example
 * new Terra_URL_Health_Check((object) [
 *     'interval' => 86400,  // Daily (24 hours in seconds)
 *     'email' => 'admin@example.com',
 *     'url' => 'https://example.com',
 * ]);
 */

require get_template_directory() . '/functions/framework/includes/url_health_check/saveReport.php';
require get_template_directory() . '/functions/framework/includes/url_health_check/showTable.php';

class Terra_URL_Health_Check {

    /** @var int Cron job interval in seconds */
    public $interval;

    /** @var string Email recipient for alerts */
    public $email;

    /** @var string Base URL to monitor */
    public $url;

    /**
     * Constructor for Terra_URL_Health_Check.
     *
     * @param object $config Configuration with interval, email, and url.
     */
    public function __construct($config) {
        $this->interval = $config->interval; // Set the interval
        $this->email = $config->email;       // Set the email
        $this->url = $config->url;           // Set the URL

        add_action('admin_menu', array($this, 'add_url_health_chequer_menu'));
    }

    /**
     * Initializes URL health check: starts cron and registers admin menu.
     *
     * @return void
     */
    public function add_url_health_chequer_menu() {
        $this->start_cronjob();
        if(is_user_logged_in() && is_admin()){
            $this->create_admin_menu_page();
        }
    }

    /**
     * Starts a cron job to periodically run URL health checks.
     *
     * @return void
     */
    public function start_cronjob() {
        $urlHealthChequerCron = new Call_Cronjob((object) array(
            'cronName' => 'every_day',
            'interval' =>  $this->interval,
            'functionName' => 'url_health_checker_content',
        ));
        $urlHealthChequerCron->call_cronjob();
    }

    /**
     * Adds URL Health Check submenu page under System Warning.
     *
     * @return void
     */
    public function create_admin_menu_page(){
        // Use WordPress's add_menu_page to create a custom admin page
        add_submenu_page(
            'system_warning',
            'Run check',
            'Run check',
            'manage_options',
            'run_check',
            'show_url_health_chequer_table'
        );
    }
}

