<div class="c--cta-a__wrapper">
    <svg class="c--cta-a__icon" viewBox="0 0 24 24" fill="none">
        <rect width="24" height="24" rx="6" fill="currentColor"/>
        <path d="M12 6v12M6 12h12" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
        <div class="f--row">
            <div class="f--col-6 f--offset-3 f--col-tabletl-12 f--offset-tabletl-0"><h2 class="c--cta-a__title"><?= $title ?></h2></div>
        </div>
    <?php if ($link): ?>
        <a class="c--cta-a__btn c--btn-a c--btn-a--second" href="<?= $link ?>">
            Contactar
            <svg class="c--icon-a" viewBox="0 0 24 24" fill="none">
                <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    <?php endif; ?>
</div>
