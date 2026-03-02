<?php
/**
 * CTA A Component
 *
 * CTA with background image, title and pill button.
 * Receives $title and $link from the flexible module.
 *
 * @package TerraFramework
 */
?>
<div class="c--cta-a">
    <div class="c--cta-a__media-wrapper js--parallax-background" <?= $bg_url ? 'style="background-image: url(\'' . esc_url($bg_url) . '\');"' : '' ?>>
        <div class="c--cta-a__wrapper">
            <div class="f--container">
                <div class="f--row">
                    <div class="f--col-12 f--col-tabletm-6">
                        <h2 class="c--cta-a__wrapper__title"><?= esc_html($title) ?></h2>
                        <?php if ($link): ?>
                            <a class="c--btn-a" href="<?= esc_url($link) ?>">
                                <span class="c--btn-a__title">Contactar</span>
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



