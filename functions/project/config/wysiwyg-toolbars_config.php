<?php
/**
 * WYSIWYG Toolbars & Custom Buttons Configuration
 *
 * Two sections:
 *   'toolbars'       — Named toolbar presets for ACF_Builder::wysiwyg(['toolbar' => 'name'])
 *   'custom_buttons' — Custom TinyMCE buttons with their JS plugin files
 *
 * Built-in ACF toolbars still available:
 *   'full'  — All TinyMCE buttons (ACF default)
 *   'basic' — Bold, italic, lists, link (ACF default)
 *
 * Available TinyMCE buttons:
 *   formatselect, styleselect, bold, italic, underline, strikethrough,
 *   bullist, numlist, blockquote, alignleft, aligncenter, alignright,
 *   link, unlink, forecolor, hr, pastetext, removeformat,
 *   charmap, outdent, indent, undo, redo, wp_more, fullscreen,
 *   wp_adv (toggles row 2)
 *
 * Headings configuration:
 *   Each toolbar can define a 'headings' array to control which block formats
 *   appear in the Format dropdown. Available values:
 *     'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre'
 *   If omitted, TinyMCE shows all formats by default.
 *   Only relevant when 'formatselect' is in the toolbar buttons.
 *
 * @package TerraProject
 */

return [

    // =========================================================================
    // TOOLBAR PRESETS
    // =========================================================================
    'toolbars' => [

        // Minimal: only inline formatting and links
        'minimal' => [
            'rows' => [
                ['bold', 'italic', 'link', 'unlink', 'removeformat'],
            ],
        ],

        // Standard: headings, formatting, lists, links
        'standard' => [
            'rows' => [
                ['formatselect', 'bold', 'italic', 'bullist', 'numlist', 'link', 'unlink', 'removeformat'],
            ],
            'headings' => ['p', 'h2'],
        ],

        // Content: full content editing with alignment (two rows, row 2 behind wp_adv toggle)
        'content' => [
            'rows' => [
                ['formatselect', 'bold', 'italic', 'underline', 'bullist', 'numlist', 'blockquote', 'wp_adv'],
                ['link', 'unlink', 'alignleft', 'aligncenter', 'alignright', 'hr', 'pastetext', 'removeformat'],
            ],
            'headings' => ['p', 'h2', 'h3', 'h4', 'h5'],
        ],

    ],

    // =========================================================================
    // CUSTOM BUTTONS
    //
    // Two targeting modes:
    //   'toolbars' => ['standard']                    — ALL fields using that toolbar
    //   'fields'   => ['main_content' => 'content']   — ONLY that field
    //               field_name => base_toolbar (which toolbar to extend)
    //               Then use: ACF_Builder::wysiwyg(['toolbar' => 'field_main_content'])
    //
    // The button_id (array key) must match the plugin name in the JS file.
    // =========================================================================
    'custom_buttons' => [

        // Highlight: on all standard & content toolbars
        'terra_highlight' => [
            'title'    => 'Highlight',
            'js_file'  => 'functions/project/utilities/wysiwyg-buttons/highlight.js',
            'toolbars' => ['content', 'standard'],
        ],

        // Add Class: only on 'rich_content' field, extends 'standard' toolbar
        // Opens popup to type a class name, wraps selection in <span class="...">
        // Use: ACF_Builder::wysiwyg(['name' => 'rich_content', 'toolbar' => 'field_rich_content'])
        'terra_add_class' => [
            'title'    => 'Add Class',
            'js_file'  => 'functions/project/utilities/wysiwyg-buttons/add_class.js',
            'fields'   => ['test' => 'minimal'],
        ],

    ],

];
