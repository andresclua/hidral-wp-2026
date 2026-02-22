<?php
/**
 * LoadMore Helper Functions
 *
 * Provides utility functions for generating LoadMore components
 * with proper security (nonce) and configuration.
 *
 * @package TerraFramework
 * @since 1.0.0
 */

/**
 * Render a LoadMore button with all required data attributes.
 *
 * @param array $args Configuration options.
 * @return void
 *
 * @example
 * // Basic usage
 * terra_loadmore_button([
 *     'container' => 'posts-grid',
 *     'template'  => 'card-a',
 * ]);
 *
 * @example
 * // With taxonomy filter
 * terra_loadmore_button([
 *     'container' => 'news-list',
 *     'template'  => 'card-b',
 *     'post_type' => 'news',
 *     'taxonomy'  => 'news_category',
 *     'term'      => 'featured',
 *     'per_page'  => 9,
 *     'label'     => 'Show More News',
 *     'class'     => 'btn btn--primary',
 * ]);
 */
function terra_loadmore_button(array $args = []): void
{
    $defaults = [
        'action'    => 'loadmore_posts',
        'container' => 'load-more',
        'template'  => 'card-a',
        'post_type' => 'post',
        'taxonomy'  => '',
        'term'      => '',
        'per_page'  => 6,
        'label'     => 'Load More',
        'class'     => 'c--btn-a js--loadmore',
        'id'        => '',
    ];

    $args = wp_parse_args($args, $defaults);

    // Generate nonce for this action
    $nonce = wp_create_nonce('terra_ajax_' . $args['action']);

    // Build attributes
    $attrs = [
        'data-load-more-action'    => esc_attr($args['action']),
        'data-load-more-container' => esc_attr($args['container']),
        'data-load-more-template'  => esc_attr($args['template']),
        'data-load-more-post-type' => esc_attr($args['post_type']),
        'data-load-more-per-page'  => esc_attr($args['per_page']),
        'data-load-more-nonce'     => esc_attr($nonce),
    ];

    // Optional taxonomy
    if ($args['taxonomy']) {
        $attrs['data-load-more-taxonomy'] = esc_attr($args['taxonomy']);
    }
    if ($args['term']) {
        $attrs['data-load-more-term'] = esc_attr($args['term']);
    }

    // Build attribute string
    $attr_string = '';
    foreach ($attrs as $key => $value) {
        $attr_string .= " {$key}=\"{$value}\"";
    }

    // Optional ID
    $id_attr = $args['id'] ? ' id="' . esc_attr($args['id']) . '"' : '';

    // Render button
    printf(
        '<button type="button" class="%s"%s%s>%s</button>',
        esc_attr($args['class']),
        $id_attr,
        $attr_string,
        esc_html($args['label'])
    );
}

/**
 * Get LoadMore button HTML as string.
 *
 * @param array $args Configuration options.
 * @return string
 */
function terra_get_loadmore_button(array $args = []): string
{
    ob_start();
    terra_loadmore_button($args);
    return ob_get_clean();
}

/**
 * Render LoadMore container wrapper.
 *
 * @param string $id        Container ID (must match 'container' in button).
 * @param string $class     Optional CSS class.
 * @param bool   $echo      Whether to echo or return.
 * @return string|void
 *
 * @example
 * terra_loadmore_container_open('posts-grid', 'grid grid--3');
 * // ... your loop here ...
 * terra_loadmore_container_close();
 */
function terra_loadmore_container_open(string $id, string $class = ''): void
{
    $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';
    printf('<div id="%s"%s>', esc_attr($id), $class_attr);
}

function terra_loadmore_container_close(): void
{
    echo '</div>';
}
