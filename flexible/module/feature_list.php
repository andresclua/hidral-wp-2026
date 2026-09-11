<?php
$title    = $module['title'] ?? '';
$subtitle = $module['subtitle'] ?? '';
$features = $module['features'] ?? [];
$hasSecondaryButton = !empty($module['has_secondary_button']);
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');
$bgColor = get_bg_color($module['bg_color'] ?? '');
$textColorClass = $bgColor ? ($bgColor['isLight'] ? 'f--color-a' : 'f--color-b') : '';
$featureCardModifier = ($bgColor && !$bgColor['isLight']) ? 'c--card-b--second' : '';
$featureBtnModifier = ($bgColor && !$bgColor['isLight']) ? 'c--btn-a--third' : '';

if (!$title && !$subtitle && !$features) {
    return;
}
?>
<section>
    <div
        class="c--feature-list-a <?= esc_attr($textColorClass) ?> <?= $spacingContentModule ?>"
        anchor-id="module-<?= $keyIndexModule ?>"
        <?php if ($bgColor) : ?>
            style="background: <?= esc_attr($bgColor['hex']) ?>;"
        <?php endif; ?>
    >
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-12">
                    <?php if ($title) : ?>
                        <h2 class="c--feature-list-a__title"><?= esc_html($title) ?></h2>
                    <?php endif; ?>
                    <?php if ($subtitle) : ?>
                        <p class="c--feature-list-a__subtitle"><?= esc_html($subtitle) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($features) : ?>
                <div class="f--row">
                    <?php foreach ($features as $feature) :
                        $card = array(
                            'title'       => $feature['title'] ?? '',
                            'description' => $feature['description'] ?? '',
                            'image'       => $feature['image'] ?? null,
                            'modifier'    => $featureCardModifier,
                        );
                    ?>
                        <div class="f--col-4">
                            <?php include(locate_template('components/card/card-b.php', false, false)); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="f--row">
                <div class="f--col-12">
                    <div class="c--feature-list-a__list-group">
                        <?php render_wp_button(array(
                            'button' => $module['primary_button'],
                            'class'  => trim('c--btn-a ' . $featureBtnModifier),
                        )); ?>
                        <?php if ($hasSecondaryButton) : ?>
                            <?php render_wp_button(array(
                                'button' => $module['secondary_button'],
                                'class'  => 'c--btn-a c--btn-a--second',
                            )); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($title);
unset($subtitle);
unset($features);
unset($hasSecondaryButton);
unset($spacingContentModule);
unset($bgColor);
unset($textColorClass);
unset($featureCardModifier);
unset($featureBtnModifier);
?>
