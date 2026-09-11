<?php
$title = $hero['title'] ?? '';
$text  = $hero['text'] ?? '';

if (!$title && !$text && empty($hero['image'])) {
    return;
}

$media = array(
    'type'    => 'image',
    'image'   => $hero['image'] ?? null,
    'sizes'   => 'large',
    'isLazy'  => false,
    'classes' => array('mediaWrapper' => '', 'mediaAsset' => ''),
);
?>
<section class="c--hero-b">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-6">
                <?php if ($title) : ?>
                    <h1 class="c--hero-b__title"><?= esc_html($title) ?></h1>
                <?php endif; ?>
                <?php if ($text) : ?>
                    <p class="c--hero-b__text"><?= esc_html($text) ?></p>
                <?php endif; ?>
                <?php render_wp_button(array('button' => $hero['custom_button'], 'class' => 'c--btn-a')); ?>
            </div>
            <div class="f--col-6">
                <?php include(locate_template('components/media/core-media.php', false, false)); ?>
            </div>
        </div>
    </div>
</section>
