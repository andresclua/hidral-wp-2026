<?php
$pretitle = $hero['pretitle'] ?? '';
$title    = $hero['title'] ?? '';
$text     = $hero['text'] ?? '';

if (!$title && !$text) {
    return;
}
?>
<section class="c--hero-c">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12">
                <?php if ($pretitle) : ?>
                    <p class="c--hero-c__pretitle"><?= esc_html($pretitle) ?></p>
                <?php endif; ?>
                <?php if ($title) : ?>
                    <h1 class="c--hero-c__title"><?= esc_html($title) ?></h1>
                <?php endif; ?>
                <?php if ($text) : ?>
                    <p class="c--hero-c__text"><?= esc_html($text) ?></p>
                <?php endif; ?>
                <?php render_wp_button(array('button' => $hero['custom_button'], 'class' => 'c--btn-a')); ?>
            </div>
        </div>
    </div>
</section>
