<?php 
    $spacingContent = get_spacing(($module['section_spacing'])); 
    $title = $module['title'];
    $link = $module['link']
?>
<section class="module_CTA c--cta-a <?= $spacingContent ?> f--background-c">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-8 f--offset-2  u--text-align-center f--col-tabletl-12 f--offset-tabletl-0">
                <h2 class="c--cta-a__title "><?= $title ?></h2>
                <?php if ($link): ?>
                        <a class="c--cta-a__btn g--btn-01 g--btn-01--second" href="<?= $link?>" class>Contactar</a>
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