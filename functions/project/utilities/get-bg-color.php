<?php
/**
 * Resolves a `bg_color` ACF field's saved key into its hex value and a
 * light/dark verdict, so a template can pair a background with readable text.
 *
 * Same brightness formula as Terra_ACF_Field_Bg_Color::is_light_color()
 * (the admin swatch border logic), just usable on the front-end too.
 *
 * @param string $key     Value saved by the `bg_color` ACF field (e.g. 'white').
 * @param string $palette Palette name passed to the field (default 'default').
 * @return array{hex: string, isLight: bool}|null Null when the key/palette isn't found.
 */
function get_bg_color($key, $palette = 'default') {
    if (!$key || !class_exists('ACF_Builder')) {
        return null;
    }

    $colors = ACF_Builder::get_config('bg_color')[$palette] ?? [];
    $hex    = $colors[$key]['color'] ?? null;

    if (!$hex) {
        return null;
    }

    $hexValue = ltrim($hex, '#');
    if (strlen($hexValue) === 3) {
        $hexValue = $hexValue[0] . $hexValue[0] . $hexValue[1] . $hexValue[1] . $hexValue[2] . $hexValue[2];
    }

    $r = hexdec(substr($hexValue, 0, 2));
    $g = hexdec(substr($hexValue, 2, 2));
    $b = hexdec(substr($hexValue, 4, 2));
    $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

    return array('hex' => $hex, 'isLight' => $brightness > 200);
}
