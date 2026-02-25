<?php
    $spacingContent = get_spacing(($module['section_spacing'] ?? ''));
    $title = $module['title'] ?? '';
    $content = $module['content'] ?? '';
?>
<section class="<?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12 f--col-tabletl-6">
                <?php if ($title): ?>
                    <h2 class="f--font-c f--mb-2"><?= $title ?></h2>
                <?php endif; ?>
                <?php if ($content): ?>
                    <div class="c--content-a">
                        <?= $content ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($title);
unset($content);
?>