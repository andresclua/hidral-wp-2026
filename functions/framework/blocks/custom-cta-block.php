<?php
$title  = $data['title'] ?? '';
$button = $data['button'] ?? null; // ACF link: ['url','title','target']

if (!$title && empty($button['url'])) return;

$btn_url    = $button['url'] ?? '';
$btn_title  = $button['title'] ?? '';
$btn_target = $button['target'] ?? '_self';
?>
<div class="custom-cta-block">
  <?php if ($title): ?>
    <p class="custom-cta-block__title"><?= wp_kses_post(nl2br($title)); ?></p>
  <?php endif; ?>

  <?php if ($btn_url && $btn_title): ?>
    <p class="custom-cta-block__button">
      <a href="<?= esc_url($btn_url); ?>" target="<?= esc_attr($btn_target); ?>" rel="<?= $btn_target === '_blank' ? 'noopener noreferrer' : ''; ?>">
        <?= esc_html($btn_title); ?>
      </a>
    </p>
  <?php endif; ?>
</div>
