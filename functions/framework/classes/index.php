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
        'Admin_Palette'           => 'Admin_Palette.php',
        'AJAX_Request'            => 'AJAX_Request.php',
        'Call_Cronjob'            => 'Call_Cronjob.php',
        'Clean_Wp'                => 'Clean_Wp.php',
        'Custom_API_Endpoint'     => 'Custom_API_Endpoint.php',
        'Custom_Blocks'           => 'Custom_Blocks.php',
        'Custom_Post_Type'        => 'Custom_Post_Type.php',
        'Custom_Search_Modules'   => 'Custom_Search_Modules.php',
        'Custom_Search_Gutenberg' => 'Custom_Search_Gutenberg.php',
        'Custom_Search_Forms'     => 'Custom_Search_Forms.php',
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
        'System_Warning'          => 'System_Warning.php',
        'Terra_URL_Health_Check'  => 'Terra_URL_Health_Check.php',
        'WP_Functionality'        => 'WP_Functionality.php',
        'WP_Vulnerability_Checker'=> 'WP_Vulnerability_Checker.php',
        'Grammar'                 => 'Grammar.php',
        'Module_Manager'          => 'Module_Manager.php',
        'Admin_Page'              => 'Admin_Page.php',
        'Post_Type_Fields'        => 'Post_Type_Fields.php',
        'ACF_Builder'             => 'ACF_Builder.php',
        'Flexible_Content'        => 'Flexible_Content.php',
        'Options_Page'            => 'Options_Page.php',
        'Schema_Builder'          => 'Schema_Builder.php',
        'WYSIWYG_Toolbars'        => 'WYSIWYG_Toolbars.php',
        'Flexible_Restrictions'   => 'Flexible_Restrictions.php',
        'Client_Documentation'    => 'Client_Documentation.php',
        'Search_Indexer'          => 'Search_Indexer.php',
        'Activity_Log'            => 'Activity_Log.php',
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
// MULTILANG SUBSYSTEM (wp-multilang — separate repo)
//
// The Multilang class, helpers, switcher template and TinyMCE plugin live
// in `functions/framework/wp-multilang/` (its own git repository). The
// framework just hands off to that repo's bootstrap — it defines path/URL
// constants, loads the class + helpers, and calls `Multilang::init()` to
// detect the URL prefix and register every hook the subsystem needs.
//
// The admin toggle (Terra → System → Multilang tab) is wired up regardless
// of enabled state so admins can turn multilang on from wp-admin; every
// other hook is a no-op until that switch is on.
// ============================================================================

$wpml_loader = __DIR__ . '/../wp-multilang/index.php';
if (file_exists($wpml_loader)) {
    require_once $wpml_loader;
}
unset($wpml_loader);

// Defensive fallback — if wp-multilang isn't installed (the directory was
// not cloned, or the project ships without it), define no-op stubs for the
// public `terra_*()` helpers so any template calling them degrades to a
// monolingual passthrough instead of fatal'ing on "undefined function".
//
// The helpers in `wp-multilang/helpers/index.php` are all wrapped in
// `if (!function_exists(...))`, so when the repo IS loaded those real
// implementations win and these stubs never get defined.
if (!function_exists('terra_multilang_switcher')) {
    function terra_multilang_enabled()           { return false; }
    function terra_languages()                   { return array(); }
    function terra_default_language()            { return ''; }
    function terra_current_language()            { return ''; }
    function terra_get_field_in($name, $lang, $post_id = false) {
        // Best effort: when multilang isn't loaded, fall back to the raw
        // get_field() value. The $lang argument is ignored — there's only
        // one language available.
        return function_exists('get_field') ? get_field($name, $post_id) : null;
    }
    function terra_language_url($lang, $url = null) {
        // Without multilang, every language resolves to the source URL
        // (or the site home when no URL was passed).
        return $url !== null ? $url : home_url('/');
    }
    function terra_multilang_switcher($custom_class = '') {
        // No-op: nothing to switch when only one language exists.
    }
}

// ============================================================================
// TERRA ADMIN MENU (registered early so all submenus can attach)
// ============================================================================

add_action('admin_menu', function () {
    add_menu_page(
        'Terra',
        'Terra',
        'manage_options',
        'terra_dashboard',
        '__return_null',
        'dashicons-shield',
        101
    );
}, 5);

// Documentation submenu removed — Client Documentation (Admin_Page) is the primary docs page.

// Schema Builder — full class with REST API, Vue UI, JSON-LD output
new Schema_Builder([
    'post_types' => ['post', 'page'],
    'capability' => 'manage_options',
]);

// ============================================================================
// CLIENT DOCUMENTATION (self-contained: admin page, AJAX, template)
// ============================================================================

new Client_Documentation();

// ============================================================================
// GRAMMAR CHECKER (class + admin pages + AJAX, all in Grammar.php)
// ============================================================================

// Deferred to after_setup_theme on purpose. This file is required from the very top of
// the theme's functions.php, while $GLOBALS['terra_grammar_config'] is only assigned at
// the end of Core::project() — which runs later, on $app->init(). Booting inline meant
// Grammar always received an empty config and silently fell back to its defaults, so
// 'post_types' stayed ['page'] no matter what the project configured. The project config
// cannot simply be loaded earlier either: it makes static ACF_Builder calls that depend
// on the autoloader registered in this very file.
//
// after_setup_theme fires once functions.php has fully run, and comfortably precedes
// everything Grammar hooks (transition_post_status, its cron events, admin_menu,
// admin_init and the wp_ajax_* endpoints).
add_action('after_setup_theme', function () {
    if (is_production_url() && Module_Manager::is_active('grammar')) {
        Grammar::boot($GLOBALS['terra_grammar_config'] ?? []);
    }
}, 5);

// ============================================================================
// ACTIVITY LOG (class + admin panel + identity modal, all in Activity_Log.php)
// ============================================================================

if (Module_Manager::is_active('activity_log')) {
    Activity_Log::boot($GLOBALS['terra_activity_log_config'] ?? []);
} elseif (wp_next_scheduled('terra_activity_log_purge')) {
    // Module was turned off — clean up its leftover purge cron. Guarded by
    // wp_next_scheduled so the class is never loaded in projects that never
    // enabled it (zero impact on pull).
    Activity_Log::unschedule_purge();
}

// ============================================================================
// TERRA ADMIN PAGES (render callbacks)
// ============================================================================

require THEME_PATH . '/functions/framework/includes/terra_admin_pages/index.php';

// ============================================================================
// VISUAL ACF MODULES (modal selection, previews, descriptions)
// ============================================================================

require THEME_PATH . '/functions/framework/includes/visual-acf-modules/init.php';

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
 * Admin_Page              - Register custom admin submenu pages from config
 * Client_Documentation    - Auto-generated client docs (admin page, AJAX, template)
 * ACF_Builder             - Island-based field builder for composing ACF fields
 * Flexible_Content        - Register ACF Flexible Content from island-composed configs
 * Flexible_Restrictions   - Restrict flexible layouts per post ID, post type, or page template
 * Options_Page            - Register ACF Options Pages from island-composed configs
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
 * Terra_URL_Health_Check  - URL health monitoring
 * Google_Search_Console   - GSC integration
 * Custom_Search_Modules   - Search modules admin panel
 * Custom_Search_Gutenberg - Gutenberg blocks usage panel (which post uses which block)
 * Custom_Search_Forms     - Contact Form 7 usage panel (which page uses which form)
 * Schema_Builder          - Visual JSON-LD schema markup builder with Vue UI
 * Grammar                 - Grammar & spelling checker (ScraperAPI + OpenAI)
 *                           Admin pages in grammar-spelling.php, controlled by Module_Manager
 *
 * INTERNATIONALIZATION (lives in wp-multilang — separate repo)
 * ------------------------------------------------------------
 * Multilang               - Multilingual subsystem: URL prefix routing, ACF field translation,
 *                           the_title/the_content/get_term swap, hreflang + Yoast SEO meta.
 *                           Class and helpers live in `functions/framework/wp-multilang/`
 *                           (its own git repository) and are loaded via `wp-multilang/index.php`.
 *                           Templates use the terra_*() helpers in `wp-multilang/helpers/index.php`.
 *                           Project opts in via functions/project/config/multilang_config.php.
 */
