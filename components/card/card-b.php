<?php
/**
 * Card B Component
 *
 * Simple card variant without image.
 * Used in loops and LoadMore AJAX responses.
 *
 * @package TerraFramework
 */

$title     = get_the_title();
$excerpt   = get_the_excerpt();
$permalink = get_permalink();
?>
<div data-card class="c--card-b">
    <a href="<?php echo esc_url($permalink); ?>" class="c--card-b__link">
        <div class="c--card-b__wrapper">
            <h2 class="c--card-b__wrapper__title"><?php echo esc_html($title); ?></h2>
            <p class="c--card-b__wrapper__subtitle"><?php echo esc_html($excerpt); ?></p>
        </div>
    </a>
</div>
