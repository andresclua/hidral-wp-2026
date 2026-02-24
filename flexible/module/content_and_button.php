
<?php
 $spacingContent = get_spacing(($module['section_spacing']));
 $title = $module['title'];
 $content = $module['content'];
 $link = $module['link'];
 $button_label = $module['button_label'];
 $color_type = $module['color_type'];
 ?>

<section class="content_and_button <?= $spacingContent ?> <?= $color_type === 'white-bg' ? 'f--background-b' : 'f--background-a' ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12 f--col-tabletl-6 f--offset-tabletl-3 u--text-align-center">
               <?php if ($title): ?>
                    <h2 class="f--font-b u--mb-4 <?= $color_type === 'white-bg' ? 'f--color-a' : 'f--color-b' ?> u--text-center"><?= $title ?></h2>
                <?php endif; ?>

                <?php if ($content): ?>
                    <div class="c--content-a<?= $color_type !== 'white-bg' ? ' c--content-a--second-color' : '' ?> u--mb-4"><?= $content ?></div>
                <?php endif; ?>

                <?php if ($link): ?>
                    <a class="c--btn-a<?= $color_type !== 'white-bg' ? ' c--btn-a--second' : '' ?>" href="<?= $link ?>"><?= $button_label ?></a>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</section>

<?php
unset($spacingContent);
unset($title);
unset($content);
unset($link);
unset($button_label);
unset($color_type);
?>