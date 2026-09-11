<?php
/**
 * Normalizes the value saved by the ACF `spacing` field type.
 *
 * Unlike other Terra projects, this field's JS (acf-spacing/assets/js/field.js)
 * already stores the final `u--pt-*`/`u--pb-*` utility classes as the value
 * (not a named preset like "top-large"), so this just handles the empty/"none"
 * states — kept as its own function so module templates all call it the same way.
 *
 * @param string $space Value saved by the `spacing` ACF field.
 * @return string Space-separated utility classes, or '' if none.
 */
function get_spacing($space) {
    if (!$space || $space === '-') {
        return '';
    }
    return $space;
}
