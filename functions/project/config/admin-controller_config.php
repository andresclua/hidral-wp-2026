<?php

/**
 * Admin Controller Configuration
 *
 * Controls admin interface elements based on template, post type, post ID, or custom conditions.
 * Allows hiding metaboxes (excerpt, thumbnail, editor, etc.), redirecting after save, and applying conditional logic.
 *
 * Configuration options:
 * - identifier: Template name, post type slug, post ID, or leave empty for condition-based
 * - match_type: 'template', 'post_type', 'post_id', or 'condition'
 * - hide_elements: Array of elements to hide (see available elements below)
 * - redirect: URL or callback function for post-save redirect (optional)
 * - condition: Callback function that returns true/false (optional, required if match_type is 'condition')
 *
 * Available hide_elements:
 * - 'excerpt'          - Post excerpt metabox
 * - 'thumbnail'        - Featured image metabox
 * - 'editor'           - Main content editor
 * - 'custom_fields'    - Custom fields metabox
 * - 'comments'         - Comments metabox
 * - 'slug'             - Slug/permalink editor
 * - 'author'           - Author metabox
 * - 'revisions'        - Revisions metabox
 * - 'page_attributes'  - Page attributes (parent, template, order)
 * - 'trackbacks'       - Trackbacks metabox
 * - 'categories'       - Categories metabox
 * - 'tags'             - Tags metabox
 */

return [

    [
        'identifier' => 'page',
        'match_type' => 'post_type',
        'hide_elements' => ['editor'],
    ],
    [
        'identifier' => 'servicios',
        'match_type' => 'post_type',
        'hide_elements' => ['editor'],
    ],
    [
        'identifier' => 'trabajos',
        'match_type' => 'post_type',
        'hide_elements' => ['editor'],
    ],

    // ============================================================================
    // TEST 2: Redirect después de guardar un post de 'trabajo'
    // ============================================================================
    // Descomenta para probar - Guarda un post de 'trabajo' y te redirigirá al listado
    /*
    [
        'identifier' => 'trabajo',
        'match_type' => 'post_type',
        'redirect' => 'edit.php?post_type=trabajo',
    ],
    */

    // ============================================================================
    // TEST 3: Ocultar elementos en post específico (cambia el ID)
    // ============================================================================
    // Cambia 42 por el ID de un post real que tengas
    /*
    [
        'identifier' => 42, // ⚠️ Cambiar por un ID real
        'match_type' => 'post_id',
        'hide_elements' => ['comments', 'slug', 'author', 'revisions'],
    ],
    */

    // ============================================================================
    // EJEMPLOS AVANZADOS (comentados)
    // ============================================================================

    // Example: Hide excerpt and thumbnail for landing page template
    /*
    [
        'identifier' => 'page-landing.php',
        'match_type' => 'template',
        'hide_elements' => ['excerpt', 'thumbnail'],
    ],
    */

    // Example: Hide editor for specific post type and redirect after save
    // [
    //     'identifier' => 'trabajo',
    //     'match_type' => 'post_type',
    //     'hide_elements' => ['excerpt'],
    // ],

    // Example: Conditional hiding based on custom field
    /*
    [
        'identifier' => 'post',
        'match_type' => 'post_type',
        'condition' => function($post_id, $post) {
            // Only apply if custom field 'minimal_editor' is true
            return get_post_meta($post_id, 'minimal_editor', true) === '1';
        },
        'hide_elements' => ['excerpt', 'custom_fields', 'comments'],
    ],
    */

    // Example: Dynamic redirect with callback function
    /*
    [
        'identifier' => 'trabajo',
        'match_type' => 'post_type',
        'redirect' => function($post_id, $post) {
            // Custom redirect logic based on post data
            $category = get_the_terms($post_id, 'category');
            if ($category && !is_wp_error($category)) {
                $cat_slug = $category[0]->slug;
                return "edit.php?post_type=trabajo&category={$cat_slug}";
            }
            return 'edit.php?post_type=trabajo';
        },
    ],
    */

    // Example: Hide all non-essential elements for ACF-only template
    /*
    [
        'identifier' => 'page-acf-only.php',
        'match_type' => 'template',
        'hide_elements' => [
            'editor',
            'excerpt',
            'custom_fields',
            'comments',
            'slug',
            'revisions',
        ],
    ],
    */

];
