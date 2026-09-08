<?php
/**
 * Block: Footnote
 *
 * Resolution order for the value:
 *   1. $data['footnote']         — set by callers that pass field values
 *                                  explicitly (Default_Blocks::render_block,
 *                                  or the wp-multilang content block renderer).
 *   2. $block['data']['footnote'] — Gutenberg preview render path.
 *   3. get_field('footnote')     — last-resort fallback (relies on ACF's
 *                                  active block context, which only exists
 *                                  inside the Gutenberg block render).
 */
$footnote = '';
if (isset($data['footnote'])) {
    $footnote = $data['footnote'];
} elseif (function_exists('is_preview') && is_preview() && isset($block['data']['footnote'])) {
    $footnote = $block['data']['footnote'];
} elseif (function_exists('get_field')) {
    $footnote = get_field('footnote');
}
include(locate_template('components/footnote/footnote.php', false, false));
