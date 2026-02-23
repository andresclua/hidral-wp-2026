<?php

/**
 * Class Admin_Controller
 *
 * Controls admin interface elements based on template, post type, or custom conditions.
 * Allows hiding metaboxes (excerpt, thumbnail, etc.), redirecting after save, and applying conditional logic.
 *
 * FEATURES:
 * - Hide metaboxes: Uses both remove_meta_box() and CSS injection for reliability
 * - Post-save redirects: Uses redirect_post_location filter for proper WordPress integration
 * - Conditional logic: Apply rules only when custom conditions are met
 * - Multiple match types: Template, post type, post ID, or custom condition
 *
 * REDIRECT BEHAVIOR:
 * - Works with classic editor and Gutenberg
 * - Preserves WordPress admin messages (e.g., "Post updated")
 * - Supports both static URLs and dynamic callbacks
 * - Automatically converts relative URLs to absolute admin URLs
 *
 * @property string $identifier Template name, post type, or post ID
 * @property string $match_type How to match: 'template', 'post_type', 'post_id', 'condition'
 * @property array $hide_elements Elements to hide: 'excerpt', 'thumbnail', 'editor', 'custom_fields', etc.
 * @property string|callable $redirect URL or callback for redirect
 * @property callable $condition Custom condition callback
 *
 * @method void init() Initialize hooks and filters
 * @method bool should_apply() Check if config should apply to current screen
 * @method void hide_metaboxes() Remove specified metaboxes
 * @method string handle_redirect() Modify post-save redirect location
 *
 * @example
 * // Hide excerpt and thumbnail for specific template
 * new Admin_Controller((object) [
 *     'identifier' => 'page-landing.php',
 *     'match_type' => 'template',
 *     'hide_elements' => ['excerpt', 'thumbnail']
 * ]);
 *
 * @example
 * // Redirect after save for specific post type
 * new Admin_Controller((object) [
 *     'identifier' => 'project',
 *     'match_type' => 'post_type',
 *     'redirect' => 'edit.php?post_type=project' // Relative URLs auto-converted to admin_url()
 * ]);
 *
 * @example
 * // Dynamic redirect based on post data
 * new Admin_Controller((object) [
 *     'identifier' => 'project',
 *     'match_type' => 'post_type',
 *     'redirect' => function($post_id, $post) {
 *         $status = get_post_meta($post_id, 'project_status', true);
 *         return "edit.php?post_type=project&status={$status}";
 *     }
 * ]);
 *
 * @example
 * // Conditional hiding based on custom logic
 * new Admin_Controller((object) [
 *     'identifier' => 'post',
 *     'match_type' => 'post_type',
 *     'condition' => function($post_id, $post) {
 *         return get_post_meta($post_id, 'use_minimal_editor', true) === '1';
 *     },
 *     'hide_elements' => ['excerpt', 'comments']
 * ]);
 */
class Admin_Controller {

    private $identifier;
    private $match_type;
    private $hide_elements;
    private $redirect;
    private $condition;

    /**
     * Constructor
     *
     * @param object $config Configuration object
     */
    public function __construct($config) {
        $this->identifier = $config->identifier ?? '';
        $this->match_type = $config->match_type ?? 'template';
        $this->hide_elements = $config->hide_elements ?? [];
        $this->redirect = $config->redirect ?? null;
        $this->condition = $config->condition ?? null;

        $this->init();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init() {
        // Hide metaboxes
        if (!empty($this->hide_elements)) {
            add_action('admin_init', [$this, 'hide_metaboxes']);
            add_action('add_meta_boxes', [$this, 'hide_metaboxes'], 99);

            if (in_array('editor', $this->hide_elements) && $this->match_type === 'post_type') {
                $pt = $this->identifier;
                add_filter('use_block_editor_for_post_type', function($use, $type) use ($pt) {
                    return $type === $pt ? false : $use;
                }, 10, 2);
            }
        }

        // Handle redirects - use filter instead of action for better compatibility
        if ($this->redirect) {
            add_filter('redirect_post_location', [$this, 'handle_redirect'], 10, 2);
        }

        // Apply custom styles to hide elements via CSS (backup method)
        add_action('admin_head', [$this, 'inject_admin_styles']);
    }

    /**
     * Check if this configuration should apply to current screen
     *
     * @param int|null $post_id Optional post ID
     * @return bool
     */
    private function should_apply($post_id = null) {
        global $post, $pagenow;

        // Use provided post_id or global post
        $current_post = $post_id ? get_post($post_id) : $post;

        if (!$current_post) {
            return false;
        }

        $applies = false;

        switch ($this->match_type) {
            case 'template':
                $template = get_post_meta($current_post->ID, '_wp_page_template', true);
                $applies = ($template === $this->identifier);
                break;

            case 'post_type':
                $applies = ($current_post->post_type === $this->identifier);
                break;

            case 'post_id':
                $applies = ($current_post->ID == $this->identifier);
                break;

            case 'condition':
                if (is_callable($this->condition)) {
                    $applies = call_user_func($this->condition, $current_post->ID, $current_post);
                }
                break;
        }

        return $applies;
    }

    /**
     * Hide specified metaboxes
     */
    public function hide_metaboxes() {
        global $post;

        if (!$this->should_apply()) {
            return;
        }

        $post_type = $post ? $post->post_type : 'post';

        foreach ($this->hide_elements as $element) {
            switch ($element) {
                case 'excerpt':
                    remove_meta_box('postexcerpt', $post_type, 'normal');
                    break;

                case 'thumbnail':
                case 'featured_image':
                    remove_meta_box('postimagediv', $post_type, 'side');
                    break;

                case 'editor':
                    remove_post_type_support($post_type, 'editor');
                    break;

                case 'custom_fields':
                    remove_meta_box('postcustom', $post_type, 'normal');
                    break;

                case 'comments':
                    remove_meta_box('commentstatusdiv', $post_type, 'normal');
                    remove_meta_box('commentsdiv', $post_type, 'normal');
                    break;

                case 'slug':
                    remove_meta_box('slugdiv', $post_type, 'normal');
                    break;

                case 'author':
                    remove_meta_box('authordiv', $post_type, 'normal');
                    break;

                case 'revisions':
                    remove_meta_box('revisionsdiv', $post_type, 'normal');
                    break;

                case 'page_attributes':
                    remove_meta_box('pageparentdiv', $post_type, 'side');
                    break;

                case 'trackbacks':
                    remove_meta_box('trackbacksdiv', $post_type, 'normal');
                    break;

                case 'categories':
                    remove_meta_box('categorydiv', $post_type, 'side');
                    break;

                case 'tags':
                    remove_meta_box('tagsdiv-post_tag', $post_type, 'side');
                    break;
            }
        }
    }

    /**
     * Inject CSS to hide elements (backup method if metabox removal doesn't work)
     */
    public function inject_admin_styles() {
        global $post;

        if (!$this->should_apply()) {
            return;
        }

        if (empty($this->hide_elements)) {
            return;
        }

        $selectors = [];

        foreach ($this->hide_elements as $element) {
            switch ($element) {
                case 'excerpt':
                    $selectors[] = '#postexcerpt';
                    break;
                case 'thumbnail':
                case 'featured_image':
                    $selectors[] = '#postimagediv';
                    break;
                case 'custom_fields':
                    $selectors[] = '#postcustom';
                    break;
                case 'comments':
                    $selectors[] = '#commentstatusdiv';
                    $selectors[] = '#commentsdiv';
                    break;
                case 'slug':
                    $selectors[] = '#slugdiv';
                    $selectors[] = '#edit-slug-box';
                    break;
                case 'author':
                    $selectors[] = '#authordiv';
                    break;
                case 'revisions':
                    $selectors[] = '#revisionsdiv';
                    break;
                case 'page_attributes':
                    $selectors[] = '#pageparentdiv';
                    break;
                case 'categories':
                    $selectors[] = '#categorydiv';
                    break;
                case 'tags':
                    $selectors[] = '#tagsdiv-post_tag';
                    break;
            }
        }

        if (!empty($selectors)) {
            echo '<style type="text/css">';
            echo implode(', ', $selectors) . ' { display: none !important; }';
            echo '</style>';
        }
    }

    /**
     * Handle post-save redirects
     *
     * @param string $location The redirect URL
     * @param int $post_id The post ID
     * @return string Modified redirect URL
     */
    public function handle_redirect($location, $post_id) {
        // Get the post object
        $post = get_post($post_id);

        if (!$post) {
            return $location;
        }

        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $location;
        }

        if (wp_is_post_revision($post_id)) {
            return $location;
        }

        // Check if configuration applies
        if (!$this->should_apply($post_id)) {
            return $location;
        }

        // Get custom redirect URL
        $redirect_url = null;

        if (is_callable($this->redirect)) {
            $redirect_url = call_user_func($this->redirect, $post_id, $post);
        } else {
            $redirect_url = $this->redirect;
        }

        // If no custom redirect, return original
        if (!$redirect_url) {
            return $location;
        }

        // Make sure it's an absolute URL
        if (!preg_match('/^https?:\/\//', $redirect_url)) {
            $redirect_url = admin_url($redirect_url);
        }

        // Preserve the message query parameter if present (e.g., message=1 for "Post updated")
        $query_args = [];

        // Parse original location to preserve important params
        $parsed_location = parse_url($location);
        if (isset($parsed_location['query'])) {
            parse_str($parsed_location['query'], $location_args);

            // Preserve WordPress admin messages
            if (isset($location_args['message'])) {
                $query_args['message'] = $location_args['message'];
            }
        }

        // Add query args to redirect URL if any
        if (!empty($query_args)) {
            $redirect_url = add_query_arg($query_args, $redirect_url);
        }

        return $redirect_url;
    }
}
