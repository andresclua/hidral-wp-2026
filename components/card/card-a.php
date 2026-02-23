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
        <a class="c--card-a__wrapper__btn c--btn-a" href="<?php the_permalink() ?>">
            Ver más
            <svg class="c--icon-a" viewBox="0 0 6 10">
                <path d="M0.8,10c0.1,0,0.3,0,0.4-0.1l4.4-4.5C5.8,5.3,5.8,5.1,5.8,5S5.6,4.7,5.6,4.6L1.1,0.1C0.9,0,0.6,0,0.4,0.2s-0.2,0.5,0,0.7
                L4.5,5L0.4,9.1c-0.2,0.2-0.2,0.5,0,0.7C0.5,9.9,0.6,10,0.8,10z"></path>
            </svg>
        </a>
    </div>
</a>

