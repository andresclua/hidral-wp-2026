<?php
    $title = $hero['title'] ?? '';
    $subtitle = $hero['subtitle'] ?? '';
    $link = $hero['link'] ?? '';
    $spacingContentHero = get_spacing($hero['section_spacing'] ?? '');
?>
<section class="<?= $spacingContentHero ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-8 f--col-tabletl-12 f--offset-2 f--offset-tabletl-0">
                <div class="c--hero-a">
                    <?php if ($title): ?>
                        <h3 class="c--hero-a__title"><?= $title ?></h3>
                    <?php endif; ?>
                    <?php if ($subtitle): ?>
                        <h1 class="c--hero-a__subtitle"><?= $subtitle ?></h1>
                    <?php endif; ?>
                    <?php if ($link): ?>
                        <a class="c--hero-a__btn c--btn-a" href="<?= $link ?>">Contactar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($title);
unset($subtitle);
unset($link);
unset($spacingContentHero);
?>
