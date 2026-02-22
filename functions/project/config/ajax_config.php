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
 * - 'key' / 'slug'       : sanitize_key()
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
            'template'  => 'key',
            'post_type' => 'key',
            'taxonomy'  => 'key',
            'term'      => 'key',
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
