<?php
/**
 * Returns a spacing class string based on a given name.
 *
 * Mobile-first approach: base value is for mobile,
 * tabletm breakpoint value is for larger screens.
 *
 * @param string $space The descriptive name for the spacing (e.g., 'top-large', 'bottom-small').
 * @return string The corresponding class string for the spacing, or an empty string if none.
 *
 * @author Nerea
 */

function get_spacing($space)
{
    $arrayValues = [
        "u--pt-10 u--pt-tabletm-15",
        "u--pt-7 u--pt-tabletm-10",
        "u--pt-4 u--pt-tabletm-7",

        "u--pb-10 u--pb-tabletm-15",
        "u--pb-7 u--pb-tabletm-10",
        "u--pb-4 u--pb-tabletm-7",

        "u--pt-10 u--pt-tabletm-15 u--pb-10 u--pb-tabletm-15",
        "u--pt-10 u--pt-tabletm-15 u--pb-7 u--pb-tabletm-10",
        "u--pt-10 u--pt-tabletm-15 u--pb-4 u--pb-tabletm-7",

        "u--pt-7 u--pt-tabletm-10 u--pb-10 u--pb-tabletm-15",
        "u--pt-7 u--pt-tabletm-10 u--pb-7 u--pb-tabletm-10",
        "u--pt-7 u--pt-tabletm-10 u--pb-4 u--pb-tabletm-7",

        "u--pt-4 u--pt-tabletm-7 u--pb-10 u--pb-tabletm-15",
        "u--pt-4 u--pt-tabletm-7 u--pb-7 u--pb-tabletm-10",
        "u--pt-4 u--pt-tabletm-7 u--pb-4 u--pb-tabletm-7",
    ];
    $arrayNames = [
        "top-large",
        "top-medium",
        "top-small",

        "bottom-large",
        "bottom-medium",
        "bottom-small",

        "top-large-bottom-large",
        "top-large-bottom-medium",
        "top-large-bottom-small",

        "top-medium-bottom-large",
        "top-medium-bottom-medium",
        "top-medium-bottom-small",

        "top-small-bottom-large",
        "top-small-bottom-medium",
        "top-small-bottom-small",
    ];
    if ($space && $space != '-') {
        $index = array_search($space, $arrayNames);
        return $index !== false ? $arrayValues[$index] : "";
    } else {
        return "";
    }
}
?>