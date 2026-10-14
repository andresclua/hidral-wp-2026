<?php
$cardTitle       = $card['title'] ?? '';
$cardDescription = $card['description'] ?? '';

if (!$cardTitle) {
    return;
}
?>
<div class="c--card-c" data-card>
    <div class="c--card-c__media-wrapper">
        <span class="c--card-c__icon"></span>
    </div>
    <div class="c--card-c__wrapper">
        <h3 class="c--card-c__title"><?= esc_html($cardTitle) ?></h3>
        <?php if ($cardDescription) : ?>
            <p class="c--card-c__content"><?= esc_html($cardDescription) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php unset($cardTitle, $cardDescription, $card); ?>
