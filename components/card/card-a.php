<?php
$cardCategory    = $card['category'] ?? '';
$cardTitle       = $card['title'] ?? '';
$cardDescription = $card['description'] ?? '';
$cardLink        = $card['link'] ?? '';
$cardImage       = $card['image'] ?? null;

if (!$cardTitle) {
    return;
}
?>
<<?= $cardLink ? 'a href="' . esc_url($cardLink) . '"' : 'div' ?> class="c--card-a" data-card>
    <?php if ($cardCategory) : ?>
        <p class="c--card-a__category"><?= esc_html($cardCategory) ?></p>
    <?php endif; ?>
    <h3 class="c--card-a__title"><?= esc_html($cardTitle) ?></h3>
    <?php if ($cardDescription) : ?>
        <p class="c--card-a__content"><?= esc_html($cardDescription) ?></p>
    <?php endif; ?>
    <?php if ($cardImage) : ?>
        <?php render_wp_image(array(
            'image' => $cardImage,
            'class' => 'c--card-a__media',
            'sizes' => 'medium',
        )); ?>
    <?php endif; ?>
</<?= $cardLink ? 'a' : 'div' ?>>
<?php unset($cardCategory, $cardTitle, $cardDescription, $cardLink, $cardImage, $card); ?>
