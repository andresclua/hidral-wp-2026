<?php
/**
 * Class Custom_Taxonomy
 * 
 * A reusable class for registering custom taxonomies in WordPress. 
 * This class allows for dynamic configuration of taxonomies by passing a configuration object during instantiation.
 *
 * @property string $taxonomy The slug for the custom taxonomy.
 * @property array $object_type The object types (post types) to which the taxonomy applies.
 * @property string $singular_name The singular name of the taxonomy.
 * @property string $plural_name The plural name of the taxonomy.
 * @property array|object $args Additional arguments for registering the taxonomy.
 *
 * @method void register_taxonomy() Registers the taxonomy using the provided configuration.
 *
 * @param object $config An object containing the configuration for the custom taxonomy.
 * @param string $config->taxonomy The slug for the taxonomy.
 * @param array $config->object_type The post types to associate with the taxonomy.
 * @param string $config->singular_name The singular label for the taxonomy.
 * @param string $config->plural_name The plural label for the taxonomy.
 * @param array|object $config->args Additional arguments for registering the taxonomy.
 *
 * TERRA EXTENSIONS:
 * - terra_hide_seo_columns: Hide SEO columns in taxonomy list (bool)
 * - terra_manage_columns: Custom columns in taxonomy list (array)
 *   Each column entry: 'column_slug' => ['label' => 'Column Title', 'field' => 'acf_field_name']
 *
 * @example
 * new Custom_Taxonomy((object) array(
 *     'taxonomy' => 'media-and-press-category',
 *     'object_type' => array('media-and-press'),
 *     'singular_name' => 'Category',
 *     'plural_name' => 'Media and Press Categories',
 *     'args' => (object) array(
 *         'rewrite' => array('slug' => 'media-and-press-category', 'with_front' => false),
 *         'terra_hide_seo_columns' => true,
 *         'terra_manage_columns' => [
 *             'related_post_type' => ['label' => 'Related Post Type', 'field' => 'related_post_type'],
 *         ],
 *     )
 * ));
 */
class Custom_Taxonomy {
    private $taxonomy;
    private $object_type;
    private $singular_name;
    private $plural_name;
    private $args;

    /**
     * Constructor for Custom_Taxonomy.
     *
     * @param object $config The configuration object for the custom taxonomy.
     */
    public function __construct($config) {
        $this->taxonomy = $config->taxonomy;
        $this->object_type = $config->object_type;
        $this->singular_name = $config->singular_name;
        $this->plural_name = $config->plural_name;
        $this->args = $config->args;

        add_action('init', array($this, 'register_taxonomy'));
    }

    /**
     * Registers the custom taxonomy using WordPress's register_taxonomy function.
     *
     * @return void
     */
    public function register_taxonomy() {
        $labels = array(
            'name' => __($this->plural_name, get_bloginfo('name')),
            'singular_name' => __($this->singular_name, get_bloginfo('name')),
            'search_items' => __('Search ' . $this->plural_name, get_bloginfo('name')),
            'all_items' => __('All ' . $this->plural_name, get_bloginfo('name')),
            'parent_item' => __('Parent ' . $this->singular_name, get_bloginfo('name')),
            'parent_item_colon' => __('Parent ' . $this->singular_name . ':', get_bloginfo('name')),
            'edit_item' => __('Edit ' . $this->singular_name, get_bloginfo('name')),
            'update_item' => __('Update ' . $this->singular_name, get_bloginfo('name')),
            'add_new_item' => __('Add New ' . $this->singular_name, get_bloginfo('name')),
            'new_item_name' => __('New ' . $this->singular_name . ' Name', get_bloginfo('name')),
            'menu_name' => __($this->plural_name, get_bloginfo('name'))
        );

        $default_args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_quick_edit' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => strtolower(str_replace(' ', '-', $this->singular_name)), 'with_front' => false)
        );

        $args = array_merge($default_args, (array) $this->args);

        register_taxonomy($this->taxonomy, $this->object_type, $args);

        $this->terra_custom_functions();
    }

    /**
     * Execute custom Terra functions after registration.
     */
    private function terra_custom_functions() {
        if (!empty($this->args['terra_hide_seo_columns'])) {
            $this->terra_hide_seo_columns_action();
        }

        if (!empty($this->args['terra_manage_columns'])) {
            $this->terra_manage_columns_action();
        }
    }

    /**
     * Hides SEO columns (Yoast, RankMath, etc.) in the taxonomy list.
     */
    private function terra_hide_seo_columns_action() {
        add_filter('manage_edit-' . $this->taxonomy . '_columns', function ($columns) {
            $seo_columns = [
                'wpseo-score', 'wpseo-title', 'wpseo-metadesc', 'wpseo-focuskw',
                'wpseo-score-readability', 'wpseo-links', 'wpseo-linked',
                'rank_math_seo_details', 'rank_math_title', 'rank_math_description', 'seo_score',
            ];
            foreach ($seo_columns as $column) {
                unset($columns[$column]);
            }
            return $columns;
        }, 10);
    }

    /**
     * Adds custom columns to the taxonomy list.
     * Each column reads an ACF field from the term.
     */
    private function terra_manage_columns_action() {
        $taxonomy = $this->taxonomy;
        $custom_columns = $this->args['terra_manage_columns'];

        add_filter('manage_edit-' . $taxonomy . '_columns', function ($columns) use ($custom_columns) {
            foreach ($custom_columns as $slug => $config) {
                $columns[$slug] = $config['label'] ?? $slug;
            }
            return $columns;
        });

        add_filter('manage_' . $taxonomy . '_custom_column', function ($content, $column_name, $term_id) use ($taxonomy, $custom_columns) {
            if (isset($custom_columns[$column_name])) {
                $field = $custom_columns[$column_name]['field'] ?? $column_name;
                $value = get_field($field, $taxonomy . '_' . $term_id);
                return $value ? ucfirst(esc_html($value)) : '—';
            }
            return $content;
        }, 10, 3);
    }
}

?>