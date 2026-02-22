<?php
/**
 * Terra Framework - Class Autoloader
 *
 * This file implements PSR-4 style autoloading for all framework classes.
 * Classes are automatically loaded when first used, improving performance
 * by only loading what's needed.
 *
 * @package suspended TerraFramework
 * @since 1.0.0
 *
 * @example
 * // Classes are loaded automatically when instantiated:
 * new Custom_Post_Type($config);  // Loads Custom_Post_Type.php
 * new Custom_Taxonomy($config);   // Loads Custom_Taxonomy.php
 */

// ============================================================================
// AUTOLOADER
// ============================================================================

spl_autoload_register(function ($class_name) {

    // Map of class names to file names (for classes with different naming)
    $class_map = [
        'Admin_Controller'        => 'Admin_Controller.php',
        'AJAX_Request'            => 'AJAX_Request.php',
        'Call_Cronjob'            => 'Call_Cronjob.php',
        'Clean_Wp'                => 'Clean_Wp.php',
        'Custom_API_Endpoint'     => 'Custom_API_Endpoint.php',
        'Custom_Blocks'           => 'Custom_Blocks.php',
        'Custom_Post_Type'        => 'Custom_Post_Type.php',
        'Custom_Search_Modules'   => 'Custom_Search_Modules.php',
        'Custom_Taxonomy'         => 'Custom_Taxonomy.php',
        'Default_Blocks'          => 'Default_Blocks.php',
        'Default_Files'           => 'Default_Files.php',
        'Default_Setup'           => 'Default_Setup.php',
        'Google_Search_Console'   => 'Google_Search_Console.php',
        'Images'                  => 'Images.php',
        'Mail_To'                 => 'Mail_To.php',
        'Manage_Columns'          => 'Manage_Columns.php',
        'Redirect_Stage_Urls'     => 'Redirect_Stage_Urls.php',
        'Security'                => 'Security.php',
        // 'System_Warning'          => 'System_Warning.php',
        'Terra_Lighthouse'        => 'Terra_Lighthouse.php',
        'Terra_URL_Health_Check'  => 'Terra_URL_Health_Check.php',
        'WP_Functionality'        => 'WP_Functionality.php',
        'WP_Vulnerability_Checker'=> 'WP_Vulnerability_Checker.php',
        'Grammar'                 => 'Grammar.php'
    ];

    // Check if class is in our map
    if (!isset($class_map[$class_name])) {
        return; // Not our class, let other autoloaders handle it
    }

    $file = __DIR__ . '/' . $class_map[$class_name];

    if (file_exists($file)) {
        require_once $file;
    }
});

// ============================================================================
// UTILITIES (Functions that need to be available globally)
// ============================================================================

require THEME_PATH . '/functions/framework/utilities/index.php';

// ============================================================================
// CLASS DOCUMENTATION INDEX
// ============================================================================
/*
 * Available Classes:
 *
 * WORDPRESS CORE EXTENSIONS
 * -------------------------
 * Custom_Post_Type        - Register custom post types with Terra extensions
 * Custom_Taxonomy         - Register custom taxonomies
 * Custom_Blocks           - Register ACF Gutenberg blocks (extends Default_Blocks)
 * Default_Blocks          - Base class for ACF block registration
 * Custom_API_Endpoint     - Register REST API endpoints
 * AJAX_Request            - Register WordPress AJAX handlers
 * Call_Cronjob            - Schedule and manage WP Cron jobs
 * Manage_Columns          - Add custom columns to admin post lists
 * Admin_Controller        - Control admin interface (hide metaboxes, redirects, conditions)
 *
 * THEME SETUP & CONFIGURATION
 * ---------------------------
 * Default_Setup           - Initialize theme (security, images, search, etc.)
 * Default_Files           - Load theme assets (enqueues, hash, variables)
 *
 * SECURITY & OPTIMIZATION
 * -----------------------
 * Security                - Security hardening (remove roles, hide WP version, etc.)
 * Clean_Wp                - Remove unnecessary WP scripts/styles, optimization
 * WP_Vulnerability_Checker- Advanced security rules (CORS, password policies, etc.)
 *
 * MEDIA & IMAGES
 * --------------
 * Images                  - Image utilities (lazy loading, srcset, responsive images)
 *
 * UTILITIES
 * ---------
 * WP_Functionality        - General WP helpers (MIME types, admin tweaks)
 * Redirect_Stage_Urls     - Stage environment URL redirections
 * Mail_To                 - Send emails via wp_mail
 *
 * TERRA MONITORING TOOLS
 * ----------------------
 * System_Warning          - Admin dashboard for monitoring tools
 * Terra_Lighthouse        - Google Lighthouse performance reports
 * Terra_URL_Health_Check  - URL health monitoring
 * Google_Search_Console   - GSC integration
 * Custom_Search_Modules   - Search modules admin panel
 */
