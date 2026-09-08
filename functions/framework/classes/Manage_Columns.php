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
 *             'reference' => 'acf',        // Will use get_field('job_title', $post_id)
 *         ],
 *         'icon' => [
 *             'label' => 'Icon',
 *             'reference' => 'acf_image',  // Will render ACF image field as <img> thumbnail
 *         ],
 *         'featured_image' => [
 *             'label' => 'Image',
 *             'reference' => 'wp',          // Will use get_the_post_thumbnail()
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
                        $field_name = $column['field'] ?? $key;
                        $value = get_field($field_name, $post_id);
                        if(is_bool($value)){
                            echo $value  ? 'Yes' : 'No';
                        }elseif($value !== '' && $value !== null && $value !== false){
                            $max = $column['max_chars'] ?? 0;
                            echo $max && mb_strlen($value) > $max ? esc_html(mb_substr($value, 0, $max)) . '…' : esc_html($value);
                        }else{
                            echo '—';
                        }
                    }elseif($column['reference'] == 'acf_image'){
                        $field_name = $column['field'] ?? $key;
                        $image = get_field($field_name, $post_id);
                        if ($image && is_array($image) && !empty($image['sizes']['thumbnail'])) {
                            echo '<img src="' . esc_url($image['sizes']['thumbnail']) . '" style="max-width:50px;height:auto;" />';
                        } elseif ($image && is_array($image) && !empty($image['url'])) {
                            echo '<img src="' . esc_url($image['url']) . '" style="max-width:50px;height:auto;" />';
                        } else {
                            echo '—';
                        }
                    }elseif($column['reference'] == 'template'){
                        $template = get_page_template_slug($post_id);
                        if ($template) {
                            $templates = wp_get_theme()->get_post_templates();
                            $post_type = get_post_type($post_id);
                            $name = $templates[$post_type][$template] ?? basename($template, '.php');
                            echo esc_html($name);
                        } else {
                            echo 'Default';
                        }
                    }elseif($column['reference'] == 'taxonomy'){
                        $terms = get_the_terms($post_id, $key);
                        if (!empty($terms) && !is_wp_error($terms)) {
                            echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
                        } else {
                            echo '—';
                        }
                    }elseif($column['reference'] == 'wp'){
                        $width = $column['width'] ?? 50;
                        if($key == 'featured_image'){
                            echo get_the_post_thumbnail($post_id, 'thumbnail', ['style' => "max-width:{$width}px;height:auto;"]);
                        }elseif($key == 'excerpt'){
                            $excerpt = get_the_excerpt($post_id);
                            echo $excerpt ? esc_html(wp_trim_words($excerpt, 15)) : '—';
                        }
                    }elseif($column['reference'] == 'callback'){
                        if (is_callable($column['callback'] ?? null)) {
                            call_user_func($column['callback'], $post_id, $column, $key);
                        }
                    }
                }
            }
        }, 10, 2);
    }
}
?>