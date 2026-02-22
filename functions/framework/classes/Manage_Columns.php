<?php
/**
 * Class Manage_Columns
 *
 * Adds custom columns to WordPress admin post list tables.
 * Supports ACF fields and featured images as column content.
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param object $config Configuration object
 * @param string $config->post_type The post type to add columns to
 * @param array  $config->columns   Array of column definitions
 *                                  Each column: ['label' => 'Column Title', 'reference' => 'acf']
 *
 * @example
 * new Manage_Columns((object) [
 *     'post_type' => 'team',
 *     'columns' => [
 *         'job_title' => [
 *             'label' => 'Job Title',
 *             'reference' => 'acf',  // Will use get_field('job_title', $post_id)
 *         ],
 *         'featured_image' => [
 *             'label' => 'Image',
 *             'reference' => 'wp',   // Will use get_the_post_thumbnail()
 *         ],
 *     ],
 * ]);
 */
class Manage_Columns
{
    /** @var string Post type slug */
    public $post_type;

    /** @var array Column definitions */
    public $columns;

    /**
     * Constructor for Manage_Columns.
     *
     * @param object $config Configuration object containing post_type and columns.
     */
    public function __construct($config)
    {
        $this->post_type = $config->post_type; // Set the interval
        $this->columns = $config->columns;       // Set the email

        add_filter('manage_' . $this->post_type . '_posts_columns', function ($columns) {
            foreach ($this->columns as $key => $column) {
                $columns['custom_' . $key] = $column['label'];
            }
            return $columns;
        });

        add_action('manage_' . $this->post_type . '_posts_custom_column', function ($column_name, $post_id) {
            foreach ($this->columns as $key => $column) {
                if ($column_name == 'custom_' . $key) {
                    if($column['reference'] == 'acf'){
                        $value = get_field($key, $post_id);
                        if(is_bool($value)){
                            echo $value  ? 'Yes' : 'No';
                        }else{
                            echo $value;
                        }
                    }else{
                        if($key == 'featured_image'){
                            echo get_the_post_thumbnail();
                        }
                    }
                }
            }
        }, 10, 2);
    }
}
?>