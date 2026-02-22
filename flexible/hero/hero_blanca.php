<section class="<?php echo $spacingContentHeroB =  get_spacing(($hero['section_spacing'])); ?> c--hero-b">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-8 f--col-tabletl-12 f--offset-2 f--offset-tabletl-0">
             
    
            <h3 class="c--hero-b__title"><?= $hero['title'] ?></h3>
                        <?php if ($hero['subtitle']): ?>
                            <h1 class="c--hero-b__subtitle"><?= $hero['subtitle'] ?></h1>
                        <?php endif; ?>

                        <?php if ($hero['link']): ?>
                            <a class="c--hero-b__btn g--btn-01" href="<?= $hero['link'] ?>" class>Contactar</a>
                        <?php endif; ?>
               
            
            </div>
        </div>
    </div>
</section>
