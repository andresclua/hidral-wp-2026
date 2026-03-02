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
$bg_url       = is_array($cta_image) ? $cta_image['url'] : $cta_image;
?>
<?php if ($cta_title): ?>
<div class="c--cta-b">
    <div class="c--cta-b__media-wrapper js--parallax-background" <?= $bg_url ? 'style="background-image: url(\'' . esc_url($bg_url) . '\');"' : '' ?>>
        <div class="c--cta-b__wrapper">
            <div class="f--container">
                <div class="f--row">
                    <div class="f--col-12 f--col-tabletm-6">
                        <h2 class="c--cta-b__wrapper__title"><?= esc_html($cta_title) ?></h2>
                        <?php if ($cta_subtitle): ?>
                            <p class="c--cta-b__wrapper__subtitle"><?= esc_html($cta_subtitle) ?></p>
                        <?php endif; ?>
                        <?php if ($cta_url): ?>
                            <a class="c--btn-a" href="<?= esc_url($cta_url['url']) ?>" <?= !empty($cta_url['target']) ? 'target="' . esc_attr($cta_url['target']) . '"' : '' ?>>
                                <span class="c--btn-a__title"><?= esc_html($cta_url['title'] ?: 'Contactar') ?></span>
                                <svg class="c--btn-a__icon" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
