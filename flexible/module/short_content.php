<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $title = $module['title'];
    $content = $module['content'];
    $color_type = $module['color_type'];
?>
<section class="module_short_content <?= $spacingContent ?> <?= $color_type === 'white-bg' ? 'f--background-b' : 'f--background-a' ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-8 f--col-tabletl-12">
                <h2 class="f--font-c u--mb-2 <?= $color_type === 'white-bg' ? 'f--color-a' : 'f--color-b' ?>"><?= $title ?></h2>
                <div class="c--content-a<?= $color_type !== 'white-bg' ? ' c--content-a--second-color' : '' ?> u--mb-2">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($title);
unset($content);
unset($color_type);
?>