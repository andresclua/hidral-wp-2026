<?php
$title    = $hero['title'] ?? '';
$subtitle = $hero['subtitle'] ?? '';
$hasSecondaryButton = !empty($hero['has_secondary_button']);
$spacingContentHero = get_spacing($hero['section_spacing'] ?? '');

if (!$title && !$subtitle && empty($hero['image'])) {
    return;
}
?>
<section class="<?= $spacingContentHero ?>">
    <div class="c--hero-a">
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-8 f--offset-2">
                    <?php if ($title) : ?>
                        <h1 class="c--hero-a__title"><?= esc_html($title) ?></h1>
                    <?php endif; ?>
                    <?php if ($subtitle) : ?>
                        <p class="c--hero-a__subtitle"><?= esc_html($subtitle) ?></p>
                    <?php endif; ?>
                    <div class="c--hero-a__list-group">
                        <?php render_wp_button(array(
                            'button' => $hero['primary_button'],
                            'class'  => 'c--btn-a',
                            'title'  => 'Contáctanos',
                        )); ?>
                        <?php if ($hasSecondaryButton) : ?>
                            <?php render_wp_button(array(
                                'button' => $hero['secondary_button'],
                                'class'  => 'c--btn-a c--btn-a--second',
                            )); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($hero['image'])) :
            $media = array(
                'type'    => 'image',
                'image'   => $hero['image'],
                'sizes'   => 'large',
                'isLazy'  => false,
                'classes' => array('mediaWrapper' => 'c--hero-a__media-wrapper', 'mediaAsset' => 'c--hero-a__media-wrapper__media'),
            );
        ?>
            <div class="f--container">
                <div class="f--row">
                    <div class="f--col-8 f--offset-2">
                        <?php include(locate_template('components/media/core-media.php', false, false)); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
unset($title);
unset($subtitle);
unset($hasSecondaryButton);
unset($spacingContentHero);
?>
