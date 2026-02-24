<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $gallery = $module['gallery'] ?? [];
?>
<section class="module_gallery_grid c--gallery-grid-a <?= $spacingContent ?>">
    <div class="f--container">
        <?php if ($gallery): ?>
            <div class="js--elastic-grid c--gallery-grid-a__wrapper">
                <?php foreach ($gallery as $index => $item): ?>
                    <div class="js--elastic-grid__item c--gallery-grid-a__wrapper__item" style="background-image: url('<?= esc_url($item['image']['url']) ?>');"></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
unset($spacingContent);
unset($gallery);
?>
