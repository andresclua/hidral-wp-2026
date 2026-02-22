<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $gallery = get_field('marquee_gallery', 'option');
?>
<section class="module_global_asset_marquee <?= $spacingContent; ?>">
    <?php if ($gallery): ?>
        <ul class="c--global-marquee-b js--marquee" data-speed="1" data-reversed="false">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <?php foreach ($gallery as $item): ?>
                    <?php $imageUrl = is_array($item['image']) && isset($item['image']['url']) ? $item['image']['url'] : false; ?>
                    <?php if ($imageUrl): ?>
                        <li class="c--global-marquee-b__item" style="background-image: url('<?= $imageUrl; ?>');">
                            <?php if ($item['title']): ?>
                                <span class="c--global-marquee-b__item__title"><?= $item['title']; ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endfor; ?>
        </ul>
    <?php endif; ?>
</section>
<?php
unset($spacingContent, $gallery);
?>