<?php
$cardTitle       = $card['title'] ?? '';
$cardDescription = $card['description'] ?? '';
$cardImage       = $card['image'] ?? null;
$cardModifier    = $card['modifier'] ?? '';

if (!$cardTitle && !$cardDescription && !$cardImage) {
    return;
}
?>
<div class="c--card-b <?= esc_attr($cardModifier) ?>" data-card>
    <?php if ($cardImage) : ?>
        <?php render_wp_image(array(
            'image' => $cardImage,
            'class' => 'c--card-b__media',
            'sizes' => 'medium',
        )); ?>
    <?php endif; ?>
    <?php if ($cardTitle) : ?>
        <h3 class="c--card-b__title"><?= esc_html($cardTitle) ?></h3>
    <?php endif; ?>
    <?php if ($cardDescription) : ?>
        <p class="c--card-b__content"><?= esc_html($cardDescription) ?></p>
    <?php endif; ?>
</div>
<?php unset($cardTitle, $cardDescription, $cardImage, $cardModifier, $card); ?>
