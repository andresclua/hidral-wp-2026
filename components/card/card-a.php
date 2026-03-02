<?php
/**
 * Card A Component
 *
 * Used in loops and LoadMore AJAX responses.
 * Expects to be called within a WordPress loop (have_posts/the_post).
 *
 * @package TerraFramework
 */

$thumbnail = get_post_thumbnail_id(get_the_ID());
$title     = get_the_title();
$excerpt   = get_the_excerpt();
$permalink = get_permalink();
?>

<a href="<?php echo esc_url($permalink); ?>" class="c--card-a">
    <div class="c--card-a__media-wrapper">
        <?php render_wp_image([
            'image' => $thumbnail,
            'sizes' => '25vw',
            'class' => 'c--card-a__media-wrapper__media',
            'isLazy' => true,
        ]); ?>
    </div>
    <div class="c--card-a__wrapper">
        <h2 class="c--card-a__wrapper__title"><?php echo esc_html($title); ?></h2>
        <p class="c--card-a__wrapper__subtitle"><?php echo esc_html($excerpt); ?></p>
        <span class="c--card-a__wrapper__btn c--btn-a">
            <span class="c--btn-a__title">Ver más</span>
            <svg class="c--btn-a__icon" viewBox="0 0 24 24" fill="none">
                <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </div>
</a>

