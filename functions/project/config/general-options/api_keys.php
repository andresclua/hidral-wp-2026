<?php
/**
 * Check if the current logged-in user is 'terradev'.
 */
if (!function_exists('_terra_is_terradev')) {
    function _terra_is_terradev() {
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
            return $user->user_login === 'terradev' || $user->user_login === 'admin';
        }
        return false;
    }
}

$fields = array();

// Terra-only fields
if (_terra_is_terradev()) {
    $fields = array_merge(
        $fields,
        ACF_Builder::note(array(
            'name'    => 'terra_api_keys_divider',
            'message' => '<strong>Terra Only</strong> — These fields are only visible to the Terra team.',
        )),
        ACF_Builder::text(array(
            'name'         => 'openai_api_key',
            'label'        => 'OpenAI API Key',
            'instructions' => 'Used by the Ask AI feature in the module picker, the Client Documentation generator, and the Grammar Checker. Get your key at platform.openai.com.',
            'rows'         => 1,
        )),
        ACF_Builder::text(array(
            'name'         => 'scraperapi_key',
            'label'        => 'ScraperAPI Key',
            'instructions' => 'Used by the Grammar Checker to scrape rendered pages. Get your key at scraperapi.com.',
            'rows'         => 1,
        )),
    );
}

return array(
    'title'  => 'API Keys',
    'fields' => $fields,
);
