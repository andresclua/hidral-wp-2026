<?php
$cardCategory = $card['category'] ?? '';
$cardTitle    = $card['title'] ?? '';
$cardContent  = $card['content'] ?? '';
$cardButton   = $card['button'] ?? null;

if (!$cardTitle) {
    return;
}
?>
<div class="c--card-d" data-card>
    <?php if ($cardCategory) : ?>
        <p class="c--card-d__category"><?= esc_html($cardCategory) ?></p>
    <?php endif; ?>
    <h2 class="c--card-d__title"><?= esc_html($cardTitle) ?></h2>
    <?php if ($cardContent) : ?>
        <div class="c--card-d__content"><?= $cardContent ?></div>
    <?php endif; ?>
    <?php render_wp_button(array('button' => $cardButton, 'class' => 'c--btn-a')); ?>
</div>
<?php unset($cardCategory, $cardTitle, $cardContent, $cardButton, $card); ?>
