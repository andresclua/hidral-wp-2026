<?php
/**
 * Returns the placeholder image URL from General Options.
 * Falls back to a transparent 1x1 pixel data URI.
 *
 * @return string Image URL or data URI
 */
function get_placeholder_image() {
    $placeholder = get_field('image_placeholder', 'option');

    if (!empty($placeholder) && is_array($placeholder) && !empty($placeholder['url'])) {
        return $placeholder['url'];
    }

    return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
}
?>