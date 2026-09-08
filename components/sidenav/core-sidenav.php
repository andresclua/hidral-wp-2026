<?php

$items = (isset($items) && is_array($items)) ? $items : [];
if (empty($items)) {
    return;
}

$sidenav_class = $sidenavClass ?? '';
$item_class    = $itemClass ?? '';
$margin        = $margin ?? '';
?>
<nav class="<?= esc_attr($sidenav_class) ?>" data-scroll-watcher<?= $margin ? ' data-scroll-watcher-margin="' . esc_attr($margin) . '"' : '' ?>>
    <?php foreach ($items as $item) :
        if (empty($item['target']) || empty($item['label'])) {
            continue;
        }
    ?>
        <a class="<?= esc_attr($item_class) ?>" href="#<?= esc_attr($item['target']) ?>" data-scroll-watcher-target="<?= esc_attr($item['target']) ?>"><?= esc_html($item['label']) ?></a>
    <?php endforeach; ?>
</nav>
<?php unset($items, $margin, $sidenavClass, $itemClass, $sidenav_class, $item_class); ?>
