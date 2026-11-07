<?php
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');
$bgColor = get_bg_color($module['bg_color'] ?? '');
$textColorClass = $bgColor ? ($bgColor['isLight'] ? 'f--color-a' : 'f--color-b') : '';
$rows = ($module['source'] ?? 'module') === 'general_options'
    ? get_field('gallery_grid_images', 'option')
    : ($module['images'] ?? []);
$images = array_values(array_filter(array_column($rows ?: [], 'image')));

if (!$images) {
    return;
}
// fewer than 4 images fit in a row; from 4 on they run as an endless strip
$isMarquee = count($images) >= 4;
// the loop needs a strip wider than the screen: repeat the images until there are enough slides
$slides = $images;
while ($isMarquee && count($slides) < 16) {
    $slides = array_merge($slides, $images);
}
?>
<section>
    <div
        class="c--gallery-grid-a <?= esc_attr($textColorClass) ?> <?= $spacingContentModule ?>"
        anchor-id="module-<?= $keyIndexModule ?>"
        <?php if ($bgColor) : ?>
            style="background: <?= esc_attr($bgColor['hex']) ?>;"
        <?php endif; ?>
    >
        <?php if ($isMarquee) : ?>
            <div class="c--gallery-grid-a__bd js--reveal-stack" data-reveal-from="bottom">
                <div class="c--gallery-grid-a__wrapper js--sliderkit-marquee" data-marquee-speed="50" data-marquee-scroll-boost="1.5">
                    <?php foreach ($slides as $i => $image) :
                        $media = array('type' => 'image', 'image' => $image, 'sizes' => 'medium', 'isLazy' => true, 'variant' => 'b');
                    ?>
                        <div class="c--gallery-grid-a__item c--gallery-grid-a__item--second" <?= $i >= count($images) ? 'aria-hidden="true"' : '' ?>>
                            <?php include(locate_template('components/media/core-media.php', false, false)); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="f--container">
                <div class="f--row c--gallery-grid-a__list-group js--reveal-stack" data-reveal-from="bottom">
                    <?php foreach ($images as $image) :
                        $media = array('type' => 'image', 'image' => $image, 'sizes' => 'medium', 'isLazy' => true, 'variant' => 'b');
                    ?>
                        <div class="f--col-6 f--col-tabletm-3">
                            <div class="c--gallery-grid-a__item">
                                <?php include(locate_template('components/media/core-media.php', false, false)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
unset($spacingContentModule);
unset($bgColor);
unset($textColorClass);
unset($rows);
unset($images);
unset($isMarquee);
unset($slides);
unset($media);
?>
