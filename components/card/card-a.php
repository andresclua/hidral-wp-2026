<?php
/**
 * Card A Component
 *
 * Used in loops and LoadMore AJAX responses.
 * Expects to be called within a WordPress loop (have_posts/the_post).
 *
 * @package TerraFramework
 */

$thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://picsum.photos/400/300';
$title     = get_the_title();
$excerpt   = get_the_excerpt();
$permalink = get_permalink();
?>
<div data-card class="c--card-a">
    <a href="<?php echo esc_url($permalink); ?>" class="c--card-a__link">
        <div class="c--card-a__media-wrapper">
            <img class="c--card-a__media-wrapper__media" src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>">
        </div>
        <div class="c--card-a__wrapper">
            <h2 class="c--card-a__wrapper__title"><?php echo esc_html($title); ?></h2>
            <p class="c--card-a__wrapper__subtitle"><?php echo esc_html($excerpt); ?></p>
        </div>
    </a>
</div>
