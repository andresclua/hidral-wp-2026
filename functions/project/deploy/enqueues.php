<?php

/**
 * Enqueue scripts and styles based on the environment
 *
 * This code snippet is responsible for enqueuing the project's JavaScript and CSS files, 
 * with different behaviors depending on whether the environment is in development or production mode.
 *
 * 1. If `IS_VITE_DEVELOPMENT` is set to `true` in local-variable.php, the function assumes the environment is in development mode.
 *    - It injects a script tag into the `<head>` of the document for Vite's Hot Module Replacement (HMR) feature, 
 *      which enables live reloading during development. The script points to the local Vite development server.
 * 
 * 2. If `IS_VITE_DEVELOPMENT` is `false` in local-variable.php, indicating a production environment:
 *    - It enqueues the minified and hashed JavaScript file from the `dist` directory. The `hash` in the filename 
 *      is replaced by the actual hash generated during the build process, ensuring cache busting.
 *    - A filter is applied to modify the script tag, setting its `type` attribute to `module` for ES module compatibility.
 *    - The corresponding CSS file is also enqueued, with a similar hash in its filename for cache busting.
 * 
 * @hook wp_enqueue_scripts  This hook runs when scripts and styles are enqueued in WordPress.
 */

add_action( 'wp_enqueue_scripts', function() {

    if (defined('IS_VITE_DEVELOPMENT') && IS_VITE_DEVELOPMENT === true) {
        function vite_head_module_hook() {
            echo '<script>window.defined = { ajaxUrl: "' . admin_url('admin-ajax.php') . '" };</script>';
            echo '<script type="module" crossorigin src="http://localhost:9090/src/js/Project.js"></script>';
            echo '<script type="module" crossorigin src="http://localhost:9090/src/scss/style.scss"></script>';
        }
        add_action('wp_head', 'vite_head_module_hook');        
    } else {
        add_action('wp_head', function() {
            echo '<script>window.defined = { ajaxUrl: "' . admin_url('admin-ajax.php') . '" };</script>';
        });
        wp_enqueue_script('project-build', get_template_directory_uri() . '/dist/Project.'.hash.'.js', [], null, true);
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ($handle === 'project-build') {
                $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
        wp_enqueue_style('project-build',  get_template_directory_uri() . '/dist/ProjectStyles.' . hash . '.css');
    }
});

// Admin Backend Only (Exclusive CSS for Admin)
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style('admin-backend-style', get_template_directory_uri() . '/dist/Appbackend.'.hash.'.css');
});

?>