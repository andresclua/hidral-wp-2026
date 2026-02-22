<?php

/**
 * Grammar Class
 *
 * Validates grammar and spelling on published posts and taxonomy archives
 * using the Spling API. Sends email notifications with the report.
 *
 * Requires:
 * - SPLING_API_KEY constant defined (or set via options)
 * - Mail_To class for email notifications
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @example
 * // Define API key in wp-config.php or theme config
 * define('SPLING_API_KEY', 'your_api_key_here');
 *
 * // Instantiate in theme setup
 * new Grammar([
 *     'post_types' => ['post', 'page'],
 *     'taxonomies' => ['category', 'post_tag'],
 *     'notify_emails' => ['editor@example.com', 'writer@example.com'],
 *     'language' => 'en-US'
 * ]);
 */
class Grammar {

    private const API_BASE_URL = 'https://api.spl.ing/v1.0';

    private string $api_key;
    private array $post_types;
    private array $taxonomies;
    private array $notify_emails;
    private string $language;

    /**
     * Constructor for Grammar.
     *
     * @param array $config Configuration options:
     *   - post_types: Array of post types to check (default: ['post', 'page'])
     *   - taxonomies: Array of taxonomies to check (default: [])
     *   - notify_emails: Array of emails to send reports (default: [admin email])
     *   - language: Preferred language for checking (default: 'en-US')
     */
    public function __construct(array $config = []) {
        // Try constant first, then helper function
        $this->api_key = defined('SPLING_API_KEY') ? SPLING_API_KEY : (function_exists('get_spling_api_key') ? get_spling_api_key() : '');
        $this->post_types = $config['post_types'] ?? ['post', 'page'];
        $this->taxonomies = $config['taxonomies'] ?? [];
        $this->notify_emails = $config['notify_emails'] ?? [get_option('admin_email')];
        $this->language = $config['language'] ?? 'en-US';

        if (empty($this->api_key)) {
            error_log('Grammar: Spling API key not found. Grammar checking disabled.');
            return;
        }

        $this->init();
    }

    /**
     * Build the Spling report card URL.
     *
     * @param string $uuid Report UUID.
     * @return string Full report card URL.
     */
    private function build_report_url(string $uuid): string {
        $website = wp_parse_url(home_url(), PHP_URL_HOST);
        return "https://www.spl.ing/report-card/?website={$website}&uuid={$uuid}";
    }

    /**
     * Initialize hooks.
     */
    protected function init(): void {
        // Hook for posts/pages/CPTs
        add_action('transition_post_status', [$this, 'on_post_publish'], 10, 3);

        // Hooks for taxonomies
        add_action('created_term', [$this, 'on_term_save'], 10, 3);
        add_action('edited_term', [$this, 'on_term_save'], 10, 3);
    }

    /**
     * Triggered when a term is created or edited.
     *
     * @param int $term_id Term ID.
     * @param int $tt_id Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public function on_term_save(int $term_id, int $tt_id, string $taxonomy): void {
        // Check if taxonomy should be validated
        if (!in_array($taxonomy, $this->taxonomies, true)) {
            return;
        }

        $term = get_term($term_id, $taxonomy);

        if (is_wp_error($term) || !$term) {
            return;
        }

        $term_url = get_term_link($term);

        if (is_wp_error($term_url)) {
            error_log("Grammar: Could not get term link for term ID {$term_id}");
            return;
        }

        // Create grammar report for taxonomy archive
        $this->create_term_report($term, $taxonomy, $term_url);
    }

    /**
     * Create a grammar report for a taxonomy term.
     *
     * @param WP_Term $term Term object.
     * @param string $taxonomy Taxonomy slug.
     * @param string $url Term archive URL.
     */
    protected function create_term_report(\WP_Term $term, string $taxonomy, string $url): void {
        $response = $this->api_request('create_report', [
            'website' => home_url(),
            'pages' => [$url],
            'num_unselected_pages' => 0,
            'config' => [
                'preferredLanguage' => $this->language
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('Grammar: API error - ' . $response->get_error_message());
            return;
        }

        if (!empty($response['uuid'])) {
            $typo_count = $this->get_report_typo_count($response['uuid']);
            $this->send_term_notification($term, $taxonomy, $url, $response['uuid'], $typo_count);
        }
    }

    /**
     * Send email notification for a term report.
     *
     * @param WP_Term $term Term object.
     * @param string $taxonomy Taxonomy slug.
     * @param string $url Term URL.
     * @param string $uuid Report UUID.
     * @param int $typo_count Number of issues found.
     */
    protected function send_term_notification(\WP_Term $term, string $taxonomy, string $url, string $uuid, int $typo_count = 0): void {
        $report_url = $this->build_report_url($uuid);

        $subject = sprintf(
            '[Grammar Check] %d issue%s found in %s: %s',
            $typo_count,
            $typo_count !== 1 ? 's' : '',
            $taxonomy,
            $term->name
        );

        $message = $this->build_term_email_message($term, $taxonomy, $url, $report_url, $typo_count);

        foreach ($this->notify_emails as $email) {
            new Mail_To((object) [
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
            ]);
        }

        error_log("Grammar: Report created for term '{$term->name}' ({$taxonomy}) - {$report_url}");
    }

    /**
     * Build the HTML email message for a term.
     *
     * @param WP_Term $term Term object.
     * @param string $taxonomy Taxonomy slug.
     * @param string $url Term URL.
     * @param string $report_url Spling report URL.
     * @param int $typo_count Number of issues found.
     * @return string HTML message.
     */
    protected function build_term_email_message(\WP_Term $term, string $taxonomy, string $url, string $report_url, int $typo_count = 0): string {
        $issues_label = $typo_count !== 1 ? 'issues' : 'issue';
        $issues_color = $typo_count > 0 ? '#e74c3c' : '#27ae60';

        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Grammar Check Report - Taxonomy</h2>
            <p>A grammar and spelling check has been completed for the following taxonomy term:</p>

            <p style='font-size: 24px; font-weight: bold; color: {$issues_color}; margin: 16px 0;'>
                {$typo_count} {$issues_label} found
            </p>

            <table style='border-collapse: collapse; margin: 20px 0;'>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>Term Name:</td>
                    <td style='padding: 8px;'>{$term->name}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>Taxonomy:</td>
                    <td style='padding: 8px;'>{$taxonomy}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>URL:</td>
                    <td style='padding: 8px;'><a href='{$url}'>{$url}</a></td>
                </tr>
            </table>

            <p style='margin: 20px 0;'>
                <a href='{$report_url}'
                   style='background-color: #0073aa; color: white; padding: 12px 24px;
                          text-decoration: none; border-radius: 4px; display: inline-block;'>
                    View Full Report
                </a>
            </p>

            <p style='color: #666; font-size: 12px;'>
                This is an automated message from the Grammar Check system powered by Spling.
            </p>
        </body>
        </html>
        ";
    }

    /**
     * Triggered when post status changes.
     * Only runs when post is published (new or updated).
     *
     * @param string $new_status New post status.
     * @param string $old_status Old post status.
     * @param WP_Post $post Post object.
     */
    public function on_post_publish(string $new_status, string $old_status, \WP_Post $post): void {
        // Only check on publish
        if ($new_status !== 'publish') {
            return;
        }

        // Check if post type should be validated
        if (!in_array($post->post_type, $this->post_types, true)) {
            return;
        }

        // Get the post URL
        $post_url = get_permalink($post->ID);

        if (!$post_url) {
            error_log("Grammar: Could not get permalink for post ID {$post->ID}");
            return;
        }

        // Create grammar report
        $this->create_report($post, $post_url);
    }

    /**
     * Create a grammar report via Spling API.
     *
     * @param WP_Post $post Post object.
     * @param string $url URL to check.
     */
    protected function create_report(\WP_Post $post, string $url): void {
        $response = $this->api_request('create_report', [
            'website' => home_url(),
            'pages' => [$url],
            'num_unselected_pages' => 0,
            'config' => [
                'preferredLanguage' => $this->language
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('Grammar: API error - ' . $response->get_error_message());
            return;
        }

        if (!empty($response['uuid'])) {
            $typo_count = $this->get_report_typo_count($response['uuid']);
            $this->send_notification($post, $url, $response['uuid'], $typo_count);
        }
    }

    /**
     * Make a POST request to the Spling API.
     *
     * @param string $endpoint API endpoint (without base URL).
     * @param array $body Request body.
     * @return array|WP_Error Response data or error.
     */
    protected function api_request(string $endpoint, array $body = []): array|\WP_Error {
        $response = wp_remote_post(self::API_BASE_URL . '/' . $endpoint, [
            'headers' => [
                'Authorization' => $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ]);

        return $this->parse_api_response($response);
    }

    /**
     * Make a GET request to the Spling API.
     *
     * @param string $endpoint API endpoint (without base URL).
     * @param array $query Query parameters.
     * @return array|WP_Error Response data or error.
     */
    protected function api_get(string $endpoint, array $query = []): array|\WP_Error {
        $url = self::API_BASE_URL . '/' . $endpoint;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => $this->api_key,
            ],
            'timeout' => 30,
        ]);

        return $this->parse_api_response($response);
    }

    /**
     * Parse an API response.
     *
     * @param array|WP_Error $response wp_remote response.
     * @return array|WP_Error Parsed data or error.
     */
    private function parse_api_response($response): array|\WP_Error {
        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code < 200 || $code >= 300) {
            return new \WP_Error(
                'spling_api_error',
                $data['message'] ?? "API returned status code {$code}"
            );
        }

        return $data ?? [];
    }

    /**
     * Get the report detail including typo count.
     *
     * @param string $uuid Report UUID.
     * @return int Number of issues found, 0 if unavailable.
     */
    protected function get_report_typo_count(string $uuid): int {
        $detail = $this->api_get('get_report_card_detail', ['uuid' => $uuid]);

        if (is_wp_error($detail)) {
            error_log('Grammar: Could not fetch report detail - ' . $detail->get_error_message());
            return 0;
        }

        return (int) ($detail['rule_count'] ?? 0);
    }

    /**
     * Send email notification with the report link.
     *
     * @param WP_Post $post Post object.
     * @param string $url Checked URL.
     * @param string $uuid Report UUID.
     * @param int $typo_count Number of issues found.
     */
    protected function send_notification(\WP_Post $post, string $url, string $uuid, int $typo_count = 0): void {
        $report_url = $this->build_report_url($uuid);

        $subject = sprintf(
            '[Grammar Check] %d issue%s found in: %s',
            $typo_count,
            $typo_count !== 1 ? 's' : '',
            $post->post_title
        );

        $message = $this->build_email_message($post, $url, $report_url, $typo_count);

        foreach ($this->notify_emails as $email) {
            new Mail_To((object) [
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
            ]);
        }

        error_log("Grammar: Report created for '{$post->post_title}' - {$report_url}");
    }

    /**
     * Build the HTML email message.
     *
     * @param WP_Post $post Post object.
     * @param string $url Checked URL.
     * @param string $report_url Spling report URL.
     * @param int $typo_count Number of issues found.
     * @return string HTML message.
     */
    protected function build_email_message(\WP_Post $post, string $url, string $report_url, int $typo_count = 0): string {
        $issues_label = $typo_count !== 1 ? 'issues' : 'issue';
        $issues_color = $typo_count > 0 ? '#e74c3c' : '#27ae60';

        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Grammar Check Report</h2>
            <p>A grammar and spelling check has been completed for the following content:</p>

            <p style='font-size: 24px; font-weight: bold; color: {$issues_color}; margin: 16px 0;'>
                {$typo_count} {$issues_label} found
            </p>

            <table style='border-collapse: collapse; margin: 20px 0;'>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>Post Title:</td>
                    <td style='padding: 8px;'>{$post->post_title}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>Post Type:</td>
                    <td style='padding: 8px;'>{$post->post_type}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>URL:</td>
                    <td style='padding: 8px;'><a href='{$url}'>{$url}</a></td>
                </tr>
                <tr>
                    <td style='padding: 8px; font-weight: bold;'>Author:</td>
                    <td style='padding: 8px;'>" . get_the_author_meta('display_name', $post->post_author) . "</td>
                </tr>
            </table>

            <p style='margin: 20px 0;'>
                <a href='{$report_url}'
                   style='background-color: #0073aa; color: white; padding: 12px 24px;
                          text-decoration: none; border-radius: 4px; display: inline-block;'>
                    View Full Report
                </a>
            </p>

            <p style='color: #666; font-size: 12px;'>
                This is an automated message from the Grammar Check system powered by Spling.
            </p>
        </body>
        </html>
        ";
    }

    /**
     * Check all published pages and taxonomy terms.
     * Returns results array for display (no emails sent).
     *
     * @return array Array of results: [ ['url', 'title', 'type', 'issues', 'report_url'], ... ]
     */
    public function check_all_pages(): array {
        $results = [];

        // Check published posts for each configured post type
        $posts = get_posts([
            'post_type'      => $this->post_types,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);

        foreach ($posts as $post) {
            $url = get_permalink($post->ID);
            if (!$url) continue;

            $post_type_obj = get_post_type_object($post->post_type);
            $type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

            $result = [
                'url'        => $url,
                'title'      => $post->post_title,
                'type'       => $type_label,
                'issues'     => 0,
                'report_url' => '',
            ];

            $response = $this->api_request('create_report', [
                'website' => home_url(),
                'pages'   => [$url],
                'num_unselected_pages' => 0,
                'config'  => ['preferredLanguage' => $this->language],
            ]);

            if (!is_wp_error($response) && !empty($response['uuid'])) {
                $result['issues']     = $this->get_report_typo_count($response['uuid']);
                $result['report_url'] = $this->build_report_url($response['uuid']);
            }

            $results[] = $result;
        }

        // Check taxonomy terms
        foreach ($this->taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);

            if (is_wp_error($terms)) continue;

            foreach ($terms as $term) {
                $url = get_term_link($term);
                if (is_wp_error($url)) continue;

                $tax_obj = get_taxonomy($taxonomy);
                $type_label = $tax_obj ? $tax_obj->labels->singular_name : $taxonomy;

                $result = [
                    'url'        => $url,
                    'title'      => $term->name,
                    'type'       => $type_label,
                    'issues'     => 0,
                    'report_url' => '',
                ];

                $response = $this->api_request('create_report', [
                    'website' => home_url(),
                    'pages'   => [$url],
                    'num_unselected_pages' => 0,
                    'config'  => ['preferredLanguage' => $this->language],
                ]);

                if (!is_wp_error($response) && !empty($response['uuid'])) {
                    $result['issues']     = $this->get_report_typo_count($response['uuid']);
                    $result['report_url'] = $this->build_report_url($response['uuid']);
                }

                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Manually check a URL (for testing or manual triggers).
     *
     * @param string $url URL to check.
     * @param array|null $emails Override notification emails.
     * @return array|WP_Error API response or error.
     */
    public function check_url(string $url, ?array $emails = null): array|\WP_Error {
        $response = $this->api_request('create_report', [
            'website' => home_url(),
            'pages' => [$url],
            'num_unselected_pages' => 0,
            'config' => [
                'preferredLanguage' => $this->language
            ]
        ]);

        if (!is_wp_error($response) && !empty($response['uuid'])) {
            $send_to = $emails ?? $this->notify_emails;
            $typo_count = $this->get_report_typo_count($response['uuid']);
            $issues_label = $typo_count !== 1 ? 'issues' : 'issue';
            $issues_color = $typo_count > 0 ? '#e74c3c' : '#27ae60';

            $report_url = $this->build_report_url($response['uuid']);
            $subject = sprintf('[Grammar Check] %d %s found in: %s', $typo_count, $issues_label, $url);
            $message = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Grammar Check Report - Manual</h2>
                <p>A manual grammar check has been requested for:</p>
                <p><a href='{$url}'>{$url}</a></p>
                <p style='font-size: 24px; font-weight: bold; color: {$issues_color}; margin: 16px 0;'>
                    {$typo_count} {$issues_label} found
                </p>
                <p style='margin: 20px 0;'>
                    <a href='{$report_url}'
                       style='background-color: #0073aa; color: white; padding: 12px 24px;
                              text-decoration: none; border-radius: 4px; display: inline-block;'>
                        View Full Report
                    </a>
                </p>
            </body>
            </html>
            ";

            foreach ($send_to as $email) {
                new Mail_To((object) [
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                ]);
            }
        }

        return $response;
    }
}
