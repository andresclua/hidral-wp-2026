<?php
/**
 * Class AJAX_Request
 *
 * Terra-style helper to register secure AJAX endpoints in WordPress.
 * Supports nonce verification, capability checks, input sanitization,
 * and standardized JSON responses.
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param object $config Configuration object
 * @param string   $config->action       The AJAX action name (required)
 * @param callable $config->callback     The callback function or method (required)
 * @param bool     $config->public       Allow non-logged-in users (default: false)
 * @param bool     $config->verify_nonce Verify nonce for security (default: true)
 * @param string   $config->nonce_name   Custom nonce field name (default: 'nonce')
 * @param string   $config->capability   Required user capability (default: null)
 * @param string   $config->method       HTTP method: 'POST', 'GET', or 'ANY' (default: 'POST')
 * @param array    $config->sanitize     Sanitization rules for fields
 * @param array    $config->required     Required fields that must be present
 *
 * @example
 * ! Basic usage
 * new AJAX_Request((object) [
 *     'action' => 'my_loadmore',
 *     'callback' => 'my_loadmore_callback',
 *     'public' => true,
 * ]);
 *
 * @example
 * ! Advanced usage with security and sanitization
 * new AJAX_Request((object) [
 *     'action' => 'submit_form',
 *     'callback' => 'handle_form_submission',
 *     'public' => false,
 *     'verify_nonce' => true,
 *     'capability' => 'edit_posts',
 *     'method' => 'POST',
 *     'required' => ['email', 'message'],
 *     'sanitize' => [
 *         'email' => 'email',           // sanitize_email
 *         'message' => 'textarea',       // sanitize_textarea_field
 *         'page' => 'int',               // intval
 *         'title' => 'text',             // sanitize_text_field
 *         'content' => 'html',           // wp_kses_post
 *         'url' => 'url',                // esc_url_raw
 *         'ids' => 'array_int',          // array of integers
 *     ],
 * ]);
 */
class AJAX_Request
{
    /** @var object Original config */
    private $config;

    /** @var string AJAX action name */
    private $action;

    /** @var callable Callback function */
    private $callback;

    /** @var bool Allow public (non-logged-in) access */
    private $public;

    /** @var bool Verify nonce */
    private $verify_nonce;

    /** @var string Nonce field name */
    private $nonce_name;

    /** @var string|null Required capability */
    private $capability;

    /** @var string HTTP method */
    private $method;

    /** @var array Sanitization rules */
    private $sanitize;

    /** @var array Required fields */
    private $required;

    /**
     * Constructor.
     *
     * @param object $config Configuration object.
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Initialize the AJAX handler.
     */
    protected function init(): void
    {
        $config = $this->config;

        // Required
        $this->action = isset($config->action) ? (string) $config->action : '';

        if (empty($this->action)) {
            return;
        }

        // Callback
        $this->callback = $this->resolve_callback($config);

        // Options with defaults
        $this->public       = isset($config->public) ? (bool) $config->public : false;
        $this->verify_nonce = isset($config->verify_nonce) ? (bool) $config->verify_nonce : true;
        $this->nonce_name   = isset($config->nonce_name) ? (string) $config->nonce_name : 'nonce';
        $this->capability   = isset($config->capability) ? (string) $config->capability : null;
        $this->method       = isset($config->method) ? strtoupper((string) $config->method) : 'POST';
        $this->sanitize     = isset($config->sanitize) && is_array($config->sanitize) ? $config->sanitize : [];
        $this->required     = isset($config->required) && is_array($config->required) ? $config->required : [];

        // Register hooks
        add_action('wp_ajax_' . $this->action, [$this, 'handle_request']);

        if ($this->public) {
            add_action('wp_ajax_nopriv_' . $this->action, [$this, 'handle_request']);
        }
    }

    /**
     * Handle the AJAX request with security checks.
     */
    public function handle_request(): void
    {
        // Check HTTP method
        if ($this->method !== 'ANY' && $_SERVER['REQUEST_METHOD'] !== $this->method) {
            $this->send_error('invalid_method', 'Invalid request method.', 405);
        }

        // Verify nonce
        if ($this->verify_nonce) {
            $nonce = $this->get_param($this->nonce_name);

            if (!$nonce || !wp_verify_nonce($nonce, $this->get_nonce_action())) {
                $this->send_error('invalid_nonce', 'Security check failed.', 403);
            }
        }

        // Check capability
        if ($this->capability && !current_user_can($this->capability)) {
            $this->send_error('forbidden', 'You do not have permission to perform this action.', 403);
        }

        // Check required fields
        foreach ($this->required as $field) {
            $value = $this->get_param($field);
            if ($value === null || $value === '') {
                $this->send_error('missing_field', "Required field '{$field}' is missing.", 400);
            }
        }

        // Sanitize inputs and build data array
        $data = $this->get_sanitized_data();

        // Call the callback with sanitized data
        call_user_func($this->callback, $data, $this);
    }

    /**
     * Get a parameter from POST or GET.
     *
     * @param string $key Parameter name.
     * @return mixed|null
     */
    public function get_param(string $key)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return null;
    }

    /**
     * Get all parameters, sanitized according to rules.
     *
     * @return array
     */
    public function get_sanitized_data(): array
    {
        $data = [];
        $source = $this->method === 'GET' ? $_GET : $_POST;

        foreach ($source as $key => $value) {
            if ($key === 'action' || $key === $this->nonce_name) {
                continue; // Skip WordPress action and nonce
            }

            $data[$key] = $this->sanitize_field($key, $value);
        }

        return $data;
    }

    /**
     * Sanitize a single field.
     *
     * @param string $key   Field name.
     * @param mixed  $value Field value.
     * @return mixed
     */
    protected function sanitize_field(string $key, $value)
    {
        if (!isset($this->sanitize[$key])) {
            // Default: sanitize as text
            return is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
        }

        $rule = $this->sanitize[$key];

        // Callable custom sanitizer
        if (is_callable($rule)) {
            return call_user_func($rule, $value);
        }

        // Built-in sanitizers
        switch ($rule) {
            case 'int':
            case 'integer':
                return intval($value);

            case 'float':
            case 'number':
                return floatval($value);

            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'email':
                return sanitize_email($value);

            case 'url':
                return esc_url_raw($value);

            case 'text':
            case 'string':
                return sanitize_text_field($value);

            case 'textarea':
                return sanitize_textarea_field($value);

            case 'html':
                return wp_kses_post($value);

            case 'key':
            case 'slug':
                return sanitize_key($value);

            case 'filename':
            case 'file':
                return sanitize_file_name($value);

            case 'array_int':
                return is_array($value) ? array_map('intval', $value) : [intval($value)];

            case 'array_text':
                return is_array($value) ? array_map('sanitize_text_field', $value) : [sanitize_text_field($value)];

            case 'raw':
            case 'none':
                return $value; // No sanitization

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Get the nonce action name.
     *
     * @return string
     */
    public function get_nonce_action(): string
    {
        return 'terra_ajax_' . $this->action;
    }

    /**
     * Generate a nonce for this action.
     * Use this in your templates to output the nonce.
     *
     * @return string
     */
    public function create_nonce(): string
    {
        return wp_create_nonce($this->get_nonce_action());
    }

    /**
     * Send a success response.
     *
     * @param array  $data    Response data.
     * @param string $message Optional message.
     */
    public static function send_success(array $data = [], string $message = 'Success'): void
    {
        wp_send_json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 200);
    }

    /**
     * Send an error response.
     *
     * @param string $code    Error code.
     * @param string $message Error message.
     * @param int    $status  HTTP status code.
     */
    public static function send_error(string $code, string $message, int $status = 400): void
    {
        wp_send_json([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $status);
    }

    /**
     * Send a paginated response (for load more).
     *
     * @param string $html     HTML content.
     * @param bool   $has_more Whether there are more items.
     * @param int    $page     Current page.
     * @param int    $total    Total items (optional).
     */
    public static function send_paginated(string $html, bool $has_more, int $page = 1, int $total = 0): void
    {
        wp_send_json([
            'success' => true,
            'data'    => [
                'html'     => $html,
                'has_more' => $has_more,
                'page'     => $page,
                'total'    => $total,
            ],
        ], 200);
    }

    /**
     * Resolve callback from config.
     *
     * @param object $config Configuration object.
     * @return callable
     */
    private function resolve_callback($config): callable
    {
        if (!isset($config->callback)) {
            return function () {
                self::send_error('no_callback', 'Missing callback for this AJAX action.', 500);
            };
        }

        if ($config->callback instanceof \Closure || is_callable($config->callback)) {
            return $config->callback;
        }

        if (is_string($config->callback) && function_exists($config->callback)) {
            return $config->callback;
        }

        return function () {
            self::send_error('invalid_callback', 'Invalid callback for this AJAX action.', 500);
        };
    }
}

/**
 * Helper function to create nonce for an AJAX action.
 * Use in templates: terra_ajax_nonce('my_action')
 *
 * @param string $action The AJAX action name.
 * @return string The nonce.
 */
function terra_ajax_nonce(string $action): string
{
    return wp_create_nonce('terra_ajax_' . $action);
}
