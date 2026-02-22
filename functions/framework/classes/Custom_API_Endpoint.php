<?php

/**
 * Class Custom_API_Endpoint
 *
 * Terra-style helper to register a REST API endpoint in WordPress.
 * - Uses an `init()` method called from the constructor (Terra convention).
 * - Supports native WP REST sanitization/validation via `args`.
 * - Optionally supports a global `sanitize` callable that runs BEFORE the callback.
 *
 * ---------------------------------------------------------------------------
 * WHAT DOES "SANITIZE" MEAN?
 * Sanitizing is "cleaning/normalizing" incoming values (usually from the user)
 * so your code receives predictable, safe-to-handle data types.
 *
 * In WP REST, the best place to sanitize is `args => [ param => sanitize_callback ]`.
 * WordPress will run those callbacks BEFORE your endpoint callback runs.
 *
 * ---------------------------------------------------------------------------
 * EXAMPLES (NUMBER, ID, STRING)
 *
 * 1) NUMBER (e.g. page)
 *    - Goal: ensure the value is an integer >= 1
 *
 *    'args' => [
 *      'page' => [
 *        'default' => 1,
 *        'sanitize_callback' => 'absint', // converts to a non-negative integer
 *        'validate_callback' => function ($value) {
 *          return $value >= 1;
 *        },
 *      ],
 *    ]
 *
 *    Example inputs:
 *      page=" 3 "      -> absint => 3
 *      page="3abc"     -> absint => 3
 *      page="-2"       -> absint => 2
 *      page="hello"    -> absint => 0 (then validate fails if you require >=1)
 *
 * 2) ID (e.g. post_id)
 *    - Goal: ensure it's a positive integer. Often also validate the post exists.
 *
 *    'args' => [
 *      'post_id' => [
 *        'required' => true,
 *        'sanitize_callback' => 'absint',
 *        'validate_callback' => function ($value) {
 *          return $value > 0;
 *        },
 *      ],
 *    ]
 *
 *    Optional stronger validation:
 *      'validate_callback' => function ($value) {
 *        $value = absint($value);
 *        return $value > 0 && get_post($value);
 *      }
 *
 * 3) STRING (e.g. q / search)
 *    - Goal: remove HTML, trim weird whitespace, keep it as plain text.
 *
 *    'args' => [
 *      'q' => [
 *        'default' => '',
 *        'sanitize_callback' => 'sanitize_text_field',
 *      ],
 *    ]
 *
 *    Example inputs:
 *      q="  hello  "                 -> "hello"
 *      q="<b>hi</b>"                 -> "hi"
 *      q="<script>alert(1)</script>" -> "alert(1)"
 *
 * ---------------------------------------------------------------------------
 * CONFIG
 * @param object $config
 * @param string $config->namespace  e.g. 'tf_api/v1' (no leading slash recommended)
 * @param string $config->route      e.g. '/another-endpoint'
 * @param string|array $config->methods e.g. 'GET' or ['GET','POST']
 * @param string $config->method     (alias for methods)
 * @param callable $config->callback (preferred; callable)
 * @param string $config->callback_function (alias for callback; function name as string)
 * @param callable $config->permission_callback Optional. Defaults to __return_true
 * @param array $config->args Optional. WP REST args (sanitize_callback, validate_callback, required, default, etc.)
 * @param callable $config->sanitize Optional. Global sanitizer that runs before callback.
 *
 * @example
 * new Custom_API_Endpoint((object) array(
 *   'namespace' => 'tf_api/v1',
 *   'route' => '/search',
 *   'methods' => 'GET',
 *   'callback_function' => 'tf_api_search',
 *   'args' => [
 *     'page' => [
 *       'default' => 1,
 *       'sanitize_callback' => 'absint',
 *       'validate_callback' => function ($value) { return $value >= 1; },
 *     ],
 *     'post_id' => [
 *       'required' => true,
 *       'sanitize_callback' => 'absint',
 *       'validate_callback' => function ($value) { return $value > 0; },
 *     ],
 *     'q' => [
 *       'default' => '',
 *       'sanitize_callback' => 'sanitize_text_field',
 *     ],
 *   ],
 * ));
 */
class Custom_API_Endpoint
{
    private $config;

    private $namespace;
    private $route;
    private $methods;
    private $callback;
    private $permission_callback;
    private $args;
    private $sanitize;

    public function __construct($config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Terra-style init.
     */
    protected function init(): void
    {
        $config = $this->config;

        $this->namespace = isset($config->namespace) ? (string) $config->namespace : 'tf_api/v1';
        $this->route     = isset($config->route) ? (string) $config->route : '/';
        $this->methods   = $this->resolve_methods($config);

        $this->permission_callback = (isset($config->permission_callback) && is_callable($config->permission_callback))
            ? $config->permission_callback
            : '__return_true';

        $this->args = (isset($config->args) && is_array($config->args))
            ? $config->args
            : array();

        // Optional global sanitizer (runs before callback)
        $this->sanitize = (isset($config->sanitize) && is_callable($config->sanitize))
            ? $config->sanitize
            : null;

        // Resolve and wrap the callback so we can run global sanitization first (if provided).
        $resolved_callback = $this->resolve_callback($config);
        $this->callback = $this->wrap_callback($resolved_callback);

        add_action('rest_api_init', array($this, 'register'));
    }

    /**
     * Register the route in WP REST API.
     */
    public function register(): void
    {
        register_rest_route($this->namespace, $this->route, array(
            'methods'             => $this->methods,
            'callback'            => $this->callback,
            'permission_callback' => $this->permission_callback,
            'args'                => $this->args,
        ));
    }

    /**
     * Normalize methods from config.
     */
    private function resolve_methods($config)
    {
        if (isset($config->methods)) {
            return $config->methods;
        }

        if (isset($config->method)) {
            return $config->method;
        }

        return 'GET';
    }

    /**
     * Resolve callback from config.
     */
    private function resolve_callback($config)
    {
        // Preferred: callable callback
        if (isset($config->callback) && is_callable($config->callback)) {
            return $config->callback;
        }

        // Back-compat: callback_function name
        if (isset($config->callback_function) && is_string($config->callback_function) && function_exists($config->callback_function)) {
            return $config->callback_function;
        }

        // Safe fallback (returns error)
        return function () {
            return new WP_Error(
                'tf_api_missing_callback',
                'Missing or invalid callback for this endpoint.',
                array('status' => 500)
            );
        };
    }

    /**
     * Wrap callback to run optional global sanitizer before the real handler.
     */
    private function wrap_callback($resolved_callback)
    {
        $sanitizer = $this->sanitize;

        return function (WP_REST_Request $request) use ($resolved_callback, $sanitizer) {

            // Optional global sanitizer
            if ($sanitizer) {
                call_user_func($sanitizer, $request);
            }

            // Run the actual callback
            return call_user_func($resolved_callback, $request);
        };
    }
}
