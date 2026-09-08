<?php
/**
 * AJAX Configuration
 *
 * Each item should have:
 * - action: string (required) - The AJAX action name
 * - callback: callable (required) - The callback function
 * - public: bool (optional) - Allow non-logged-in users (default: false)
 * - verify_nonce: bool (optional) - Verify nonce for security (default: true)
 * - method: string (optional) - HTTP method: 'POST', 'GET', 'ANY' (default: 'POST')
 * - required: array (optional) - Required fields
 * - sanitize: array (optional) - Sanitization rules per field
 *
 * Sanitization types:
 * - 'int' / 'integer'    : intval()
 * - 'float' / 'number'   : floatval()
 * - 'bool' / 'boolean'   : filter_var FILTER_VALIDATE_BOOLEAN
 * - 'email'              : sanitize_email()
 * - 'url'                : esc_url_raw()
 * - 'text' / 'string'    : sanitize_text_field()
 * - 'textarea'           : sanitize_textarea_field()
 * - 'html'               : wp_kses_post()
 * - 'key' / 'slug'       : sanitize_key() — AVOID: 'key' collides with PHP's key() via is_callable(), use 'text' instead
 * - 'filename' / 'file'  : sanitize_file_name()
 * - 'array_int'          : array of integers
 * - 'array_text'         : array of sanitized text
 * - 'raw' / 'none'       : no sanitization
 * - callable             : custom sanitizer function
 */

return [

    /**
     * LoadMore Posts - Generic handler
     *
     * Supports:
     * - Multiple post types via 'post_type' param
     * - Template selection via 'template' param
     * - Taxonomy filtering via 'taxonomy' and 'term' params
     */
    [
        'action'       => 'loadmore_posts',
        'public'       => true,
        'verify_nonce' => true,
        'method'       => 'POST',
        'required'     => ['page', 'per_page', 'template'],
        'sanitize'     => [
            'page'      => 'int',
            'per_page'  => 'int',
            'template'  => 'text',
            'post_type' => 'text',
            'taxonomy'  => 'text',
            'term'      => 'text',
        ],
        'callback' => function ($data) {
            $page      = $data['page'] ?? 1;
            $per_page  = $data['per_page'] ?? 6;
            $template  = $data['template'] ?? 'card-b';
            $post_type = $data['post_type'] ?? 'post';
            $taxonomy  = $data['taxonomy'] ?? '';
            $term      = $data['term'] ?? '';

            // Build query args
            $args = [
                'post_type'      => $post_type,
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'post_status'    => 'publish',
            ];

            // Add taxonomy filter if provided
            if ($taxonomy && $term) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => $term,
                    ],
                ];
            }

            $query = new WP_Query($args);
            $html = '';

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    // Load template from components/card/{template}.php
                    $template_path = get_template_directory() . "/components/card/{$template}.php";

                     ob_start();
                        include $template_path;
                        $html .= ob_get_clean();
                }
                wp_reset_postdata();
            }

            $has_more = $page < $query->max_num_pages;
            $total    = $query->found_posts;

            AJAX_Request::send_paginated($html, $has_more, $page, $total);
        },
    ],

    /**
     * Modal Content — render a PHP component server-side and return its HTML.
     *
     * The trigger passes a `template` (a theme-relative path to a PHP component,
     * e.g. 'components/card/card-x.php') and an `id` (the post whose ACF/data the
     * template renders). Used by the core-btn "ajax" modal variation to inject
     * content on open without shipping it in the initial page payload.
     */
    [
        'action'       => 'modal_content',
        'public'       => true,
        'verify_nonce' => true,
        'method'       => 'POST',
        'required'     => ['template', 'id'],
        'sanitize'     => [
            'template' => 'text',
            'id'       => 'int',
        ],
        'callback' => function ($data) {
            // Shared with core-btn's htmlContent variation (see utilities/render-modal-template.php).
            $html = terra_render_modal_template($data['template'], $data['id']);

            if ($html === '') {
                AJAX_Request::send_error('content_not_found', 'Modal content not found.');
            }

            AJAX_Request::send_success(['html' => $html]);
        },
    ],

    /**
     * CSV Importer — processes one row at a time
     */
    [
        'action'       => 'terra_csv_import',
        'public'       => false,
        'verify_nonce' => true,
        'method'       => 'POST',
        'capability'   => 'edit_posts',
        'required'     => ['post_type', 'row_data'],
        'sanitize'     => [
            'post_type' => 'text',
            'row_data'  => 'raw',
            'row_index' => 'int',
        ],
        'callback' => function ($data) {
            $post_type = $data['post_type'];
            $row_data  = json_decode(stripslashes($data['row_data']), true);

            if (!$row_data || !is_array($row_data)) {
                AJAX_Request::send_error('invalid_data', 'Invalid row data.');
            }

            // Extract mapped fields
            $post_title   = '';
            $post_content = '';
            $post_excerpt = '';
            $thumbnail_url = '';
            $meta_title   = '';
            $meta_desc    = '';
            $taxonomies   = []; // ['taxonomy_slug' => 'term1, term2']
            $acf_fields   = []; // ['field_name' => 'value']

            foreach ($row_data as $col => $field) {
                $value = $field['value'] ?? '';
                $type  = $field['type'] ?? '';
                $name  = $field['name'] ?? '';

                switch ($type) {
                    case 'post_title':
                        $post_title = sanitize_text_field($value);
                        break;
                    case 'post_content':
                        $post_content = wp_kses_post($value);
                        break;
                    case 'post_excerpt':
                        $post_excerpt = sanitize_textarea_field($value);
                        break;
                    case 'thumbnail_url':
                        $thumbnail_url = esc_url_raw($value);
                        break;
                    case 'meta_title':
                        $meta_title = sanitize_text_field($value);
                        break;
                    case 'meta_description':
                        $meta_desc = sanitize_textarea_field($value);
                        break;
                    case 'taxonomy':
                        if ($name) {
                            $taxonomies[$name] = sanitize_text_field($value);
                        }
                        break;
                    case 'acf_field':
                        if ($name) {
                            $acf_fields[$name] = $value;
                        }
                        break;
                }
            }

            if (empty($post_title)) {
                AJAX_Request::send_error('no_title', 'Row has no title.');
            }

            // Create post
            $post_id = wp_insert_post([
                'post_type'    => $post_type,
                'post_title'   => $post_title,
                'post_content' => $post_content,
                'post_excerpt' => $post_excerpt,
                'post_status'  => 'draft',
            ], true);

            if (is_wp_error($post_id)) {
                AJAX_Request::send_error('insert_failed', $post_id->get_error_message());
            }

            // Featured image from URL
            if ($thumbnail_url) {
                $attach_id = terra_csv_sideload_image($thumbnail_url, $post_id);
                if ($attach_id && !is_wp_error($attach_id)) {
                    set_post_thumbnail($post_id, $attach_id);
                }
            }

            // Yoast SEO meta
            if ($meta_title) {
                update_post_meta($post_id, '_yoast_wpseo_title', $meta_title);
            }
            if ($meta_desc) {
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
            }

            // Taxonomies (comma-separated terms)
            foreach ($taxonomies as $tax => $terms_str) {
                if (!taxonomy_exists($tax)) continue;
                $terms = array_map('trim', explode(',', $terms_str));
                $term_ids = [];

                foreach ($terms as $term_name) {
                    if (empty($term_name)) continue;
                    $existing = get_term_by('name', $term_name, $tax);
                    if ($existing) {
                        $term_ids[] = (int) $existing->term_id;
                    } else {
                        $new_term = wp_insert_term($term_name, $tax);
                        if (!is_wp_error($new_term)) {
                            $term_ids[] = (int) $new_term['term_id'];
                        }
                    }
                }

                if (!empty($term_ids)) {
                    wp_set_object_terms($post_id, $term_ids, $tax);
                }
            }

            // ACF fields
            if (function_exists('update_field')) {
                foreach ($acf_fields as $field_name => $field_value) {
                    update_field($field_name, $field_value, $post_id);
                }
            }

            AJAX_Request::send_success([
                'post_id' => $post_id,
                'title'   => $post_title,
            ], 'Row imported successfully.');
        },
    ],

    /**
     * CSV Export — get available fields for a post type
     */
    [
        'action'       => 'terra_csv_export_fields',
        'public'       => false,
        'verify_nonce' => true,
        'method'       => 'POST',
        'capability'   => 'edit_posts',
        'required'     => ['post_type'],
        'sanitize'     => [
            'post_type' => 'text',
        ],
        'callback' => function ($data) {
            $post_type = $data['post_type'];

            // Taxonomies
            $taxonomies = get_object_taxonomies($post_type, 'objects');
            $tax_list = [];
            foreach ($taxonomies as $tax) {
                if ($tax->name === 'post_format') continue;
                $tax_list[] = [
                    'slug'  => $tax->name,
                    'label' => $tax->labels->name,
                ];
            }

            // ACF fields
            $acf_fields = [];
            if (function_exists('acf_get_field_groups')) {
                $groups = acf_get_field_groups(['post_type' => $post_type]);
                foreach ($groups as $group) {
                    $fields = acf_get_fields($group['key']);
                    if ($fields) {
                        foreach ($fields as $field) {
                            // Skip layout-only fields
                            if (in_array($field['type'], ['tab', 'message', 'accordion'], true)) continue;
                            $acf_fields[] = [
                                'name'  => $field['name'],
                                'label' => $field['label'],
                                'type'  => $field['type'],
                            ];
                        }
                    }
                }
            }

            AJAX_Request::send_success([
                'taxonomies' => $tax_list,
                'acf_fields' => $acf_fields,
            ]);
        },
    ],

    /**
     * CSV Export — generate and return CSV content
     */
    [
        'action'       => 'terra_csv_export',
        'public'       => false,
        'verify_nonce' => true,
        'method'       => 'POST',
        'capability'   => 'edit_posts',
        'required'     => ['post_type', 'fields'],
        'sanitize'     => [
            'post_type'   => 'text',
            'fields'      => 'raw',
            'post_status' => 'text',
        ],
        'callback' => function ($data) {
            $post_type   = $data['post_type'];
            $fields      = json_decode(stripslashes($data['fields']), true);
            $post_status = $data['post_status'] ?? 'publish';

            if (!$fields || !is_array($fields)) {
                AJAX_Request::send_error('invalid_fields', 'No fields selected.');
            }

            $posts = get_posts([
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => $post_status,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            // Build CSV in memory
            $output = fopen('php://temp', 'r+');

            // BOM for Excel UTF-8 compatibility
            fwrite($output, "\xEF\xBB\xBF");

            // Header row
            $headers = [];
            foreach ($fields as $field) {
                $headers[] = $field['label'];
            }
            fputcsv($output, $headers);

            // Data rows
            foreach ($posts as $post) {
                $row = [];
                foreach ($fields as $field) {
                    $row[] = terra_csv_get_field_value($post, $field);
                }
                fputcsv($output, $row);
            }

            rewind($output);
            $csv_content = stream_get_contents($output);
            fclose($output);

            $filename = $post_type . '-export-' . date('Y-m-d') . '.csv';

            AJAX_Request::send_success([
                'csv_content' => $csv_content,
                'filename'    => $filename,
                'count'       => count($posts),
            ]);
        },
    ],

    /**
     * Example: Form submission with validation
     */
    // [
    //     'action'       => 'submit_contact_form',
    //     'public'       => true,
    //     'verify_nonce' => true,
    //     'method'       => 'POST',
    //     'required'     => ['name', 'email', 'message'],
    //     'sanitize'     => [
    //         'name'    => 'text',
    //         'email'   => 'email',
    //         'phone'   => 'text',
    //         'message' => 'textarea',
    //     ],
    //     'callback' => function ($data) {
    //         // Validate email
    //         if (!is_email($data['email'])) {
    //             AJAX_Request::send_error('invalid_email', 'Please enter a valid email address.');
    //         }
    //
    //         // Process form...
    //         $to = get_option('admin_email');
    //         $subject = 'New Contact Form Submission';
    //         $body = "Name: {$data['name']}\nEmail: {$data['email']}\nMessage: {$data['message']}";
    //
    //         $sent = wp_mail($to, $subject, $body);
    //
    //         if ($sent) {
    //             AJAX_Request::send_success(['redirect' => '/thank-you'], 'Message sent successfully!');
    //         } else {
    //             AJAX_Request::send_error('mail_failed', 'Failed to send message. Please try again.');
    //         }
    //     },
    // ],

];
