<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $title = $module['title'];
    $content = $module['content'];
    $color_type = $module['color_type'];
    $alignment = $module['alignment'] ?? 'left';
    $colClass = $alignment === 'center' ? 'f--col-tabletl-6 f--col-12 f--offset-tabletl-3' : 'f--col-tabletl-8 f--col-12';
    $textAlign = $alignment === 'center' ? 'u--text-align-center' : '';
?>
<section class="module_short_content <?= $spacingContent ?> <?= $color_type === 'white-bg' ? 'f--background-b' : 'f--background-a' ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="<?= $colClass ?> <?= $textAlign ?>">
                <h2 class="f--font-b u--mb-2 <?= $color_type === 'white-bg' ? 'f--color-a' : 'f--color-b' ?>"><?= $title ?></h2>
                <div class="c--content-a <?= $color_type !== 'white-bg' ? ' c--content-a--second-color' : '' ?> u--mb-2">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent, $title, $content, $color_type, $alignment, $colClass, $textAlign);
?>