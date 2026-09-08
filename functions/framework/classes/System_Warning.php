<?php
/**
 * Class System_Warning
 *
 * Central admin dashboard for Terra monitoring and diagnostic tools.
 * Provides a unified interface for URL health checks,
 * Google Search Console integration, and email notifications.
 *
 * Features:
 * - Admin menu page "System Warning" for all monitoring tools
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
 * @param bool   $config['google_search_console_enabled'] Enable GSC integration
 * @param bool   $config['mail_to_enabled']              Enable email notifications
 * @param array  $config['mail_to_config']               Email config ['email', 'subject', 'message']
 * @param bool   $config['url_health_checked_enabled']   Enable URL health monitoring
 *
 * @example
 * new System_Warning([
 *     'recipients' => ['admin@example.com'],
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

    public function __construct(array $config = []) {
        $this->config = $config;

        $this->google_search_console_enabled = !empty($config['google_search_console_enabled']);
        $this->url_health_checked_enabled    = !empty($config['url_health_checked_enabled']);
        $this->mailto_enabled                = !empty($config['mail_to_enabled']);
        $this->mail_to_config                = $config['mail_to_config'] ?? [];
        $this->recipients                    = $config['recipients'] ?? [];
        $this->interval                      = get_option('terra_sw_interval', null)
                                              ?: (function_exists('get_field') ? get_field('terra_system_warning_interval', 'option') : null);

        add_action('admin_menu', [$this, 'register_admin_pages']);
        add_action('wp_ajax_terra_save_modules', [$this, 'ajax_save_modules']);
        add_action('wp_ajax_terra_save_sw_settings', [$this, 'ajax_save_sw_settings']);

        if ($this->mailto_enabled && !empty($config['mail_to_config']) && is_production_url() && Module_Manager::is_active('mail_to')) {
            $mail = new Mail_To((object) array(
                'email' => $this->mail_to_config ['email'],
                'subject' => $this->mail_to_config['subject'],
                'message' => $this->mail_to_config['message'],
            ));
        }

        if ($this->google_search_console_enabled && is_production_url() && Module_Manager::is_active('google_search_console')) {
            new Google_Search_Console([]);
        }

        if ($this->url_health_checked_enabled && is_production_url() && Module_Manager::is_active('url_health_check')) {
            $this->set_up_terra_url_health_chequer();
        }
    }

    public function register_admin_pages() {
        // No-op: page is now registered by acf_add_options_page in system_warning/index.php
        // This keeps the method for backwards compatibility
    }

    public function set_up_terra_url_health_chequer(): void {
        new Terra_URL_Health_Check((object) [
            'email' => $this->recipients,
            'interval' =>  $this->interval?? 300000,
            'url' => get_site_url(),
        ]);
    }

    public function ajax_save_modules() {
        check_ajax_referer('terra_save_modules', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $modules = isset($_POST['modules']) ? (array) $_POST['modules'] : [];
        Module_Manager::save_modules($modules);

        wp_send_json_success(['message' => 'Modules updated. Changes take effect on next page load.']);
    }

    public function ajax_save_sw_settings() {
        check_ajax_referer('terra_save_sw_settings', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $interval = sanitize_text_field($_POST['interval'] ?? '604800');
        $raw_emails = isset($_POST['emails']) ? (array) $_POST['emails'] : [];
        $emails = array_values(array_filter(array_map('sanitize_email', $raw_emails)));

        update_option('terra_sw_interval', $interval);
        update_option('terra_sw_emails', $emails);

        wp_send_json_success(['message' => 'Settings saved.']);
    }

}
