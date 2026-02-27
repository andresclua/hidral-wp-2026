<?php
/**
 * CTA B Component (Global)
 *
 * Pulls data from General Settings > Banner (ACF Options Page).
 *
 * @package TerraFramework
 */

$cta_title    = get_field('titulo', 'option');
$cta_subtitle = get_field('subtitle', 'option');
$cta_url      = get_field('url', 'option');
$cta_image    = get_field('image', 'option');
?>
<?php if ($cta_title): ?>
<div class="c--cta-b">
    <?php if ($cta_image): ?>
        <div class="c--cta-b__media-wrapper">
            <?php render_wp_image([
                'image' => $cta_image,
                'sizes' => '100vw',
                'class' => 'c--cta-b__media-wrapper__media',
                'isLazy' => true,
            ]); ?>
        </div>
    <?php endif; ?>
    <div class="c--cta-b__wrapper">
        <h2 class="c--cta-b__wrapper__title"><?= esc_html($cta_title) ?></h2>
        <?php if ($cta_subtitle): ?>
            <p class="c--cta-b__wrapper__subtitle"><?= esc_html($cta_subtitle) ?></p>
        <?php endif; ?>
        <?php if ($cta_url): ?>
            <a class="c--cta-b__wrapper__btn c--btn-a c--btn-a--second" href="<?= esc_url($cta_url['url']) ?>" <?= !empty($cta_url['target']) ? 'target="' . esc_attr($cta_url['target']) . '"' : '' ?>>
                <?= esc_html($cta_url['title'] ?: 'Contactar') ?>
                <svg class="c--icon-a" viewBox="0 0 24 24" fill="none">
                    <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
