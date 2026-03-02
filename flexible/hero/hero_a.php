<?php
    $title = $hero['title'] ?? '';
    $subtitle = $hero['subtitle'] ?? '';
    $link = $hero['link'] ?? '';
    $spacingContentHero = get_spacing($hero['section_spacing'] ?? '');
?>
<section class="<?= $spacingContentHero ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-8 f--col-tabletl-8 f--offset-2 f--offset-tabletl-2">
                <div class="c--hero-a">
                    <?php if ($title): ?>
                        <h3 class="c--hero-a__title"><?= $title ?></h3>
                    <?php endif; ?>
                    <?php if ($subtitle): ?>
                        <h1 class="c--hero-a__subtitle"><?= $subtitle ?></h1>
                    <?php endif; ?>
                    <?php if ($link): ?>
                        <a class="c--hero-a__btn c--btn-a" href="<?= $link ?>">
                            <span class="c--btn-a__title">Contactar</span>
                            <svg class="c--btn-a__icon" viewBox="0 0 24 24" fill="none">
                                <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
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
