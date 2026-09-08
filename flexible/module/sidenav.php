<?php
$rows = $module['sidenav_items'] ?? [];
if (!$rows) {
    return;
}

// Build one entry per row; the target id is generated from the index (namespaced
// by the module index so multiple sidenavs on a page can't collide).
$sidenav = [];
foreach ($rows as $i => $row) {
    if (empty($row['title'])) {
        continue;
    }
    $sidenav[] = [
        'target'  => 'sidenav-' . $keyIndexModule . '-' . $i,
        'title'   => $row['title'],
        'content' => $row['content'] ?? '',
    ];
}
if (!$sidenav) {
    return;
}
?>
<div class="c--sidenav-a" anchor-id="module-<?= $keyIndexModule ?>">
    <?php
        $items = array_map(function ($entry) {
            return ['target' => $entry['target'], 'label' => $entry['title']];
        }, $sidenav);
        $sidenavClass = 'c--sidenav-a__nav';
        $itemClass    = 'c--sidenav-a__item';
        include(locate_template('components/sidenav/core-sidenav.php', false, false));
    ?>
    <div class="c--sidenav-a__content">
        <?php foreach ($sidenav as $entry) : ?>
            <section id="<?= esc_attr($entry['target']) ?>" class="c--sidenav-a__section"><?= wp_kses_post($entry['content']) ?></section>
        <?php endforeach; ?>
    </div>
</div>
