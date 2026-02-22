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
 * @example
 * new Custom_Taxonomy((object) array(
 *     'taxonomy' => 'media-and-press-category',
 *     'object_type' => array('media-and-press'),
 *     'singular_name' => 'Category',
 *     'plural_name' => 'Media and Press Categories',
 *     'args' => (object) array(
 *         'rewrite' => array('slug' => 'media-and-press-category', 'with_front' => false)
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
    }
}

?>