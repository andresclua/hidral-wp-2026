<?php
/**
 * Block: Highlighted
 *
 * Resolution order for the value:
 *   1. $data['highlighted']         — set by callers that pass field values
 *                                     explicitly (Default_Blocks::render_block,
 *                                     or the wp-multilang content block renderer).
 *   2. $block['data']['highlighted'] — Gutenberg preview render path.
 *   3. get_field('highlighted')     — last-resort fallback (relies on ACF's
 *                                     active block context, which only exists
 *                                     inside the Gutenberg block render).
 */
$highlighted = '';
if (isset($data['highlighted'])) {
    $highlighted = $data['highlighted'];
} elseif (function_exists('is_preview') && is_preview() && isset($block['data']['highlighted'])) {
    $highlighted = $block['data']['highlighted'];
} elseif (function_exists('get_field')) {
    $highlighted = get_field('highlighted');
}
include(locate_template('components/highlighted/highlighted.php', false, false));
