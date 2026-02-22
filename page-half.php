<?php
/*
Template Name: Page Half
*/
?>
<?php get_header() ?>

<div class="c--hero-b">
    <h2 class="c--hero-b__title">Page with elements in viewport</h2>
</div>

<div class="f--container--fluid u--overflow-hidden">
    <div class="f--row">
        <div class="f--col-12 f--col-tablets-12">
            <div
                class="c--marquee-b c--marquee-b--second js--marquee u--overflow-hidden"
                data-speed=".8"
                data-controls-on-hover="true"
                data-reversed="false"
                data-index="b0">
                <?php for ($j = 0; $j < 30; $j++) : ?>
                    <div class="c--marquee-b__item">
                        <span>text</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<div class="f--container">
    <div class="f--row">
        <div class="f--col-4">
            <h2 class="f--font-c js--reveal-stack">Move element</h2>
        </div>
    </div>
</div>

<?php get_footer() ?>