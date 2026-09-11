<?php
$title  = get_field('trusted_companies_title', 'option') ?: '';
$images = get_field('trusted_companies_images', 'option') ?: [];
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');

if (!$title && !$images) {
    return;
}
?>
<section class="<?= $spacingContentModule ?>">
    <div class="c--trusted-companies-a" anchor-id="module-<?= $keyIndexModule ?>">
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-4">
                    <?php if ($title) : ?>
                        <h2 class="c--trusted-companies-a__title"><?= esc_html($title) ?></h2>
                    <?php endif; ?>
                </div>
                <div class="f--col-8">
                    <?php if ($images) : ?>
                        <ul class="c--trusted-companies-a__list-group">
                            <?php foreach ($images as $row) :
                                $image = $row['image'] ?? null;
                                if (!$image) {
                                    continue;
                                }
                            ?>
                                <li class="c--trusted-companies-a__list-group__item">
                                    <?php render_wp_image(array(
                                        'image' => $image,
                                        'class' => 'c--trusted-companies-a__list-group__item__media',
                                        'sizes' => 'small',
                                    )); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($title);
unset($images);
unset($spacingContentModule);
?>
