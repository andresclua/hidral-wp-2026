<?php


/**
 * Class Custom_Post_Type
 *
 * A reusable class for registering custom post types in WordPress.
 * This class allows for dynamic configuration of post types by passing a configuration object during instantiation.
 *
 * TERRA EXTENSIONS:
 * - terra_hide_permalink: Hide permalink box in editor (bool)
 * - terra_hide_preview_button: Hide preview button in editor (bool)
 * - terra_hide_seo_columns: Hide SEO columns in post list (bool)
 * - terra_redirect: Redirect single post views (string path, function name, or callback)
 * - terra_manage_columns: Custom columns in post list (array)
 *
 * @property string $post_type The slug for the custom post type.
 * @property string $singular_name The singular name of the post type.
 * @property string $plural_name The plural name of the post type.
 * @property array|object $args Additional arguments for registering the post type.
 *
 * @method void register_post_type() Registers the post type using the provided configuration.
 *
 * @param object $config An object containing the configuration for the custom post type.
 * @param string $config->post_type The slug for the post type.
 * @param string $config->singular_name The singular label for the post type.
 * @param string $config->plural_name The plural label for the post type.
 * @param array|object $config->args Additional arguments for registering the post type.
 *
 * @example
 * // Basic example with custom supports
 * new Custom_Post_Type((object) array(
 *     'post_type' => 'media-and-press',
 *     'singular_name' => 'Media and Press',
 *     'plural_name' => 'Media and Press',
 *     'args' => array(
 *         'menu_icon' => 'dashicons-portfolio',
 *         'rewrite' => array('slug' => 'media-and-press', 'with_front' => false),
 *         'supports' => array('title', 'editor', 'thumbnail', 'excerpt'), // Custom supports
 *         'terra_hide_permalink' => true,
 *         'terra_redirect' => '/about-us'
 *     )
 * ));
 *
 * @example
 * // Dynamic redirect with callback function
 * new Custom_Post_Type((object) array(
 *     'post_type' => 'proyecto',
 *     'singular_name' => 'Proyecto',
 *     'plural_name' => 'Proyectos',
 *     'args' => array(
 *         'menu_icon' => 'dashicons-portfolio',
 *         'terra_redirect' => function($post_id, $post) {
 *             // Redirect based on post meta
 *             $external_url = get_post_meta($post_id, 'external_url', true);
 *             if ($external_url) {
 *                 return $external_url;
 *             }
 *             // Or redirect to category archive
 *             $terms = get_the_terms($post_id, 'project-category');
 *             if ($terms && !is_wp_error($terms)) {
 *                 return get_term_link($terms[0]);
 *             }
 *             return '/proyectos';
 *         }
 *     )
 * ));
 *
 * @example
 * // Redirect to function name
 * new Custom_Post_Type((object) array(
 *     'post_type' => 'evento',
 *     'singular_name' => 'Evento',
 *     'plural_name' => 'Eventos',
 *     'args' => array(
 *         'terra_redirect' => 'my_custom_redirect_logic' // Function name
 *     )
 * ));
 */

class Custom_Post_Type {
    private $post_type;  // Slug for the custom post type
    private $singular_name;  // Singular name for the custom post type
    private $plural_name;  // Plural name for the custom post type
    private $args;  // Additional arguments for the custom post type

    // Constructor that accepts a configuration object and assigns values to properties
    public function __construct($config) {
        $this->post_type = $config->post_type;
        $this->singular_name = $config->singular_name;
        $this->plural_name = $config->plural_name;
        $this->args = $config->args;
        // WordPress action hook to register the post type when the 'init' action is fired
        add_action('init', array($this, 'register_post_type'));
    }

    // Registers the custom post type in WordPress using the provided or default arguments
    public function register_post_type() {
        $labels = array(
            'name' => __($this->plural_name, get_bloginfo('name')),
            'singular_name' => __($this->singular_name, get_bloginfo('name')),
            'add_new' => __('Add New', get_bloginfo('name')),
            'add_new_item' => __('Add New ' . $this->singular_name, get_bloginfo('name')),
            'edit_item' => __('Edit ' . $this->singular_name, get_bloginfo('name')),
            'new_item' => __('New ' . $this->singular_name, get_bloginfo('name')),
            'all_items' => __('All ' . $this->plural_name, get_bloginfo('name')),
            'view_item' => __('View ' . $this->singular_name, get_bloginfo('name')),
            'search_items' => __('Search ' . $this->plural_name, get_bloginfo('name')),
            'not_found' => __('No ' . $this->singular_name . ' found', get_bloginfo('name')),
            'not_found_in_trash' => __('No ' . $this->plural_name . ' found in Trash', get_bloginfo('name')),
            'parent_item_colon' => '',
            'menu_name' => __($this->plural_name, get_bloginfo('name'))
        );

        // Default arguments for registering the custom post type
        $default_args = array(
            'labels' => $labels,
            'public' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'show_in_rest' => true,
            // Default supports - can be overridden in config
            'supports' => array('title', 'editor', 'thumbnail')
        );

        // Merges default arguments with any provided in the $args object
        // Note: 'supports' from config will completely replace the default
        $args = array_merge($default_args, (array) $this->args);

        // Registers the custom post type in WordPress
        register_post_type($this->post_type, $args);

        // Execute custom Terra functions after registration
        $this->terra_custom_functions();
    }

    // Executes custom functions, such as hiding the permalink or preview button based on the configuration
    public function terra_custom_functions() {

        if (!empty($this->args['terra_hide_permalink']) && $this->args['terra_hide_permalink']) {
            add_action('admin_head', [$this, 'terra_hide_permalink_action']);
        }

        if (!empty($this->args['terra_hide_preview_button']) && $this->args['terra_hide_preview_button']) {
            add_action('admin_head', [$this, 'terra_hide_preview_button_action']);
        }

        if (!empty($this->args['terra_hide_seo_columns'])) {
            $this->terra_hide_seo_columns_action();
        }

        // Existing redirect style (string path OR function name)
        if (!empty($this->args['terra_redirect'])) {
            $this->terra_redirect_action();
        }

        if (!empty($this->args['terra_manage_columns'])) {
            $this->terra_manage_columns_action();
        }
    }

    
    // Action that hides the permalink in the editor by adding CSS to the page
    public function terra_hide_permalink_action() {
        if (isset($_GET['post'])) {
            echo '<style>body.post-type-'.esc_attr($this->post_type) .' #post-body-content .inside{ display:none } </style>';
        }
    }

    /**
     * Hides the preview button in editor, and View link in post list
     */
    public function terra_hide_preview_button_action() {
        global $pagenow;

        // Only on post editor and list pages
        if (!in_array($pagenow, ['post.php', 'post-new.php', 'edit.php'])) {
            return;
        }

        // Check if it's our post type
        $current_post_type = '';
        if (isset($_GET['post_type'])) {
            $current_post_type = $_GET['post_type'];
        } elseif (isset($_GET['post'])) {
            $current_post_type = get_post_type($_GET['post']);
        }

        // Only apply CSS if it's our post type
        if ($current_post_type !== $this->post_type) {
            return;
        }

        echo '<style>
            /* Gutenberg editor - Preview button */
            .post-type-' . esc_attr($this->post_type) . ' .block-editor-post-preview__button-toggle,
            .post-type-' . esc_attr($this->post_type) . ' .editor-post-preview,
            .post-type-' . esc_attr($this->post_type) . ' .components-button.editor-post-preview__button-toggle {
                display: none !important;
            }

            /* Classic editor - Preview button */
            .post-type-' . esc_attr($this->post_type) . ' #post-preview,
            .post-type-' . esc_attr($this->post_type) . ' .preview.button {
                display: none !important;
            }

            /* Post list table - View action link */
            .post-type-' . esc_attr($this->post_type) . ' .row-actions .view,
            .post-type-' . esc_attr($this->post_type) . ' .row-actions span.view {
                display: none !important;
            }
        </style>';
    }

    /**
     * Hides SEO columns (Yoast, RankMath, etc.) in the admin post list
     */
    public function terra_hide_seo_columns_action() {
        add_filter('manage_edit-' . $this->post_type . '_columns', function($columns) {
            // Remove common SEO plugin columns
            $seo_columns = [
                'wpseo-score',           // Yoast SEO score
                'wpseo-title',           // Yoast SEO title
                'wpseo-metadesc',        // Yoast SEO description
                'wpseo-focuskw',         // Yoast focus keyword
                'wpseo-score-readability', // Yoast readability
                'wpseo-links',           // Yoast links
                'rank_math_seo_details', // Rank Math SEO
                'rank_math_title',       // Rank Math title
                'rank_math_description', // Rank Math description
                'seo_score',             // Generic SEO score
            ];

            foreach ($seo_columns as $column) {
                if (isset($columns[$column])) {
                    unset($columns[$column]);
                }
            }

            return $columns;
        }, 10);
    }
    
    /**
     * Handles redirection for single posts of the custom post type
     *
     * Supports three redirect types:
     * 1. String path: '/some-page' or 'about-us'
     * 2. Function name: 'my_custom_redirect_function'
     * 3. Callback function: function($post_id, $post) { return '/custom-url'; }
     */
    public function terra_redirect_action(): void {

        // Hook runs before template output
        add_action('template_redirect', function () {

            $redirect = $this->args['terra_redirect'] ?? null;
            if (!$redirect) return;

            // Only apply to single posts of this CPT
            if (!is_singular($this->post_type)) {
                return;
            }

            $target_url = null;
            $post_id = get_the_ID();
            $post = get_post($post_id);

            // Case 1: Callable (anonymous function or callback)
            if (is_callable($redirect)) {
                $target_url = call_user_func($redirect, $post_id, $post);
            }
            // Case 2: String function name that exists
            elseif (is_string($redirect) && function_exists($redirect)) {
                $result = call_user_func($redirect, $post_id, $post);
                // If function returns a string, use it as URL
                if (is_string($result)) {
                    $target_url = $result;
                }
                // If function does its own redirect, just return
                return;
            }
            // Case 3: Static string path
            elseif (is_string($redirect)) {
                $target_url = $redirect;
            }

            // If we have a target URL, redirect to it
            if ($target_url) {
                // If it's already a full URL, use it as-is
                if (preg_match('/^https?:\/\//', $target_url)) {
                    $url = esc_url($target_url);
                } else {
                    // Otherwise treat it as a path relative to home
                    $url = esc_url(home_url($target_url));
                }

                wp_redirect($url, 301);
                exit;
            }
        });
    }


    public function terra_manage_columns_action()
    {
        new Manage_Columns((object) array(
            'post_type' => $this->post_type,
            'columns' => $this->args['terra_manage_columns']
        ));
    }

}
?>