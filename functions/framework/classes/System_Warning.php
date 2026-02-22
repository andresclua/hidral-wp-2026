<?php
/**
 * Class System_Warning
 *
 * Central admin dashboard for Terra monitoring and diagnostic tools.
 * Provides a unified interface for performance monitoring, URL health checks,
 * Google Search Console integration, and email notifications.
 *
 * Features:
 * - Admin menu page "System Warning" for all monitoring tools
 * - Terra Lighthouse integration (PageSpeed performance reports)
 * - URL Health Check (monitors site URLs for errors)
 * - Google Search Console integration
 * - Email notifications via Mail_To
 *
 * Note: Most features only run on production URLs (is_production_url())
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param array $config Configuration options
 * @param array  $config['recipients']                   Email recipients for notifications
 * @param bool   $config['lighthouse_enabled']           Enable Lighthouse reports
 * @param string $config['lighthouse_url']               URL to analyze
 * @param bool   $config['google_search_console_enabled'] Enable GSC integration
 * @param bool   $config['mail_to_enabled']              Enable email notifications
 * @param array  $config['mail_to_config']               Email config ['email', 'subject', 'message']
 * @param bool   $config['url_health_checked_enabled']   Enable URL health monitoring
 *
 * @example
 * new System_Warning([
 *     'recipients' => ['admin@example.com'],
 *     'lighthouse_enabled' => true,
 *     'lighthouse_url' => 'https://example.com',
 *     'google_search_console_enabled' => true,
 *     'mail_to_enabled' => true,
 *     'mail_to_config' => [
 *         'email' => 'admin@example.com',
 *         'subject' => 'System Alert',
 *         'message' => 'Alert message',
 *     ],
 *     'url_health_checked_enabled' => true,
 * ]);
 */
require get_template_directory() . '/functions/framework/includes/system_warning/index.php';

class System_Warning {

    /** @var array */
    protected $config = [];

    /** @var bool */
    protected $lighthouse_enabled = false;

    /** @var bool */
    protected $mailto_enabled = false;

    /** @var bool */
    protected $google_search_console_enabled = false;

    /** @var bool */
    protected $url_health_checked_enabled = false;

    /** @var array */
    protected $mail_to_config = [];

    /** @var array */
    protected $recipients = [];

    /** @var int|null */
    protected $interval = null;

    /** @var array */
    protected $grammar_config = [];

    public function __construct(array $config = []) {
        $this->config = $config;

        $this->google_search_console_enabled = !empty($config['google_search_console_enabled']);
        $this->lighthouse_enabled            = !empty($config['lighthouse_enabled']);
        $this->url_health_checked_enabled            = !empty($config['url_health_checked_enabled']);
        $this->mailto_enabled                = !empty($config['mail_to_enabled']);
        $this->mail_to_config                = $config['mail_to_config'] ?? [];
        $this->recipients                    = $config['recipients'] ?? [];
        $this->grammar_config                = $config['grammar_config'] ?? [];

        add_action('admin_menu', [$this, 'register_admin_pages']);
        add_action('wp_ajax_terra_grammar_check_all', [$this, 'ajax_grammar_check_all']);

        if ($this->lighthouse_enabled && is_production_url()) {
            $this->set_up_terra_lighthouse();
        }

        if ($this->mailto_enabled && !empty($config['mail_to_config']) && is_production_url()) {
            $mail = new Mail_To((object) array(
                'email' => $this->mail_to_config ['email'],  // Email address to be used in the class
                'subject' => $this->mail_to_config['subject'],                // Interval (in seconds) for some functionality in the class
                'message' => $this->mail_to_config['message'] ,           // URL to be used in the class
            ));
        }

        if ($this->google_search_console_enabled && is_production_url()) {
            new Google_Search_Console([]);
        }

         if ($this->url_health_checked_enabled && is_production_url()) {
            $this->set_up_terra_url_health_chequer();
        }
    }

    protected function get_interval(): int {
        if ($this->interval === null) {
            $this->interval = get_field('terra_system_warning_interval', 'option');
        }
        return $this->interval ?? 300000;
    }

    public function register_admin_pages(): void {

        add_menu_page(
            'Dashboard',
            'System Warning',
            'manage_options',
            'system_warning',
            'show_system_warning_viewers',
            'dashicons-warning',
            101
        );
       
    }

    public function set_up_terra_lighthouse(): void {
        // new Terra_Lighthouse((object) [
        //     'email' => $this->recipients,
        //     'interval' => $this->get_interval(),
        //     'url' => get_site_url(),
        // ]);
    }

    public function set_up_terra_url_health_chequer(): void {
        new Terra_URL_Health_Check((object) [
            'email' => $this->recipients,
            'interval' =>  $this->interval?? 300000,
            'url' => get_site_url(),
        ]);
    }

    public function ajax_grammar_check_all(): void {
        check_ajax_referer('terra_grammar_check_all', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $grammar = new Grammar($this->grammar_config);
        $results = $grammar->check_all_pages();

        wp_send_json_success($results);
    }

}
