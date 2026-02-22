<?php

/**
 * API Helper Functions
 *
 * Helper functions to access API configuration
 */

if (!function_exists('get_api_config')) {
    /**
     * Get API configuration
     *
     * @param string|null $service Service name (e.g., 'spling', 'google_maps')
     * @param string|null $key Specific key to get (e.g., 'api_key', 'enabled')
     * @return mixed|null Configuration value or null if not found
     *
     * @example
     * // Get all Spling config
     * $spling = get_api_config('spling');
     *
     * @example
     * // Get Spling API key directly
     * $api_key = get_api_config('spling', 'api_key');
     *
     * @example
     * // Get all API config
     * $all_apis = get_api_config();
     */
    function get_api_config($service = null, $key = null) {
        static $api_config = null;

        // Load config once
        if ($api_config === null) {
            $config_file = THEME_PATH . '/functions/project/config/api_config.php';
            if (file_exists($config_file)) {
                $api_config = require $config_file;
            } else {
                $api_config = [];
            }
        }

        // Return all config
        if ($service === null) {
            return $api_config;
        }

        // Return specific service config
        if (!isset($api_config[$service])) {
            return null;
        }

        // Return specific key from service
        if ($key !== null) {
            return $api_config[$service][$key] ?? null;
        }

        // Return full service config
        return $api_config[$service];
    }
}

if (!function_exists('get_spling_api_key')) {
    /**
     * Get Spling API key
     *
     * Convenience function to get Spling API key
     *
     * @return string|null API key or null if not configured
     *
     * @example
     * $api_key = get_spling_api_key();
     */
    function get_spling_api_key() {
        return get_api_config('spling', 'api_key');
    }
}

if (!function_exists('is_api_enabled')) {
    /**
     * Check if an API service is enabled
     *
     * @param string $service Service name
     * @return bool True if enabled, false otherwise
     *
     * @example
     * if (is_api_enabled('spling')) {
     *     // Use Spling API
     * }
     */
    function is_api_enabled($service) {
        return get_api_config($service, 'enabled') === true;
    }
}
