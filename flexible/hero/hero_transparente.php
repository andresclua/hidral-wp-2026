<!-- Hero-a -->
<section class="<?php echo $spacingContentHeroB =  get_spacing(($hero['section_spacing'])); ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-8 f--col-tabletl-12 f--offset-2 f--offset-tabletl-0">
                <div class="c--hero-a">

                        <h3 class="c--hero-a__title"><?= $hero['title'] ?></h3>
                        <?php if ($hero['subtitle']): ?>
                            <h1 class="c--hero-a__subtitle"><?= $hero['subtitle'] ?></h1>
                        <?php endif; ?>

                        <?php if ($hero['link']): ?>
                        <a class="c--hero-a__btn c--btn-a" href="<?= $hero['link'] ?>" class>Contactar</a>
                        <?php endif; ?>
                    </div>
            </div>
        </div>
    </div>
</section>
