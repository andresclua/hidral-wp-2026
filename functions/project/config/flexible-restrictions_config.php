<?php
/**
 * Flexible Restrictions Config
 *
 * Restrict which ACF Flexible Content layouts are available
 * per post ID, post type, or page template.
 *
 * Structure:
 *   'field_name' => [
 *       [
 *           'match_type'      => 'post_id' | 'post_type' | 'page_template',
 *           'match_value'     => (int|string) value to match against,
 *           'allowed_layouts' => ['layout_a', 'layout_b'],
 *       ],
 *   ]
 *
 * Rules are evaluated in order — first match wins.
 * If no rule matches, all layouts remain available.
 *
 * @package TerraProject
 */

return [

    // -------------------------------------------------------------------------
    // 'modules' flexible content field
    // -------------------------------------------------------------------------
    // 'modules' => [
    //     [
    //         'match_type'      => 'post_id',
    //         'match_value'     => 5458,
    //         'allowed_layouts' => ['heading_with_options', 'highlighted_section'],
    //     ],
    //     [
    //         'match_type'      => 'post_type',
    //         'match_value'     => 'solutions',
    //         'allowed_layouts' => ['heading_with_options', 'highlighted_section'],
    //     ],
    // ],

];
