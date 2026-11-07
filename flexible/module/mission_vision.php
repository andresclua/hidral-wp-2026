<?php
$items = array(
    array(
        'label' => $module['vision_label'] ?? '',
        'title' => $module['vision_title'] ?? '',
        'text'  => $module['vision_text'] ?? '',
    ),
    array(
        'label' => $module['mission_label'] ?? '',
        'title' => $module['mission_title'] ?? '',
        'text'  => $module['mission_text'] ?? '',
    ),
);
?>
<section>
    <div
        class="c--pinned-scroll-a js--pinned-scroll-mission-vision"
        anchor-id="module-<?= $keyIndexModule ?>"
    >
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-12">
                    <div class="c--pinned-scroll-a__wrapper">
                        <?php foreach ($items as $item) : ?>
                            <div class="c--pinned-scroll-a__item js--pinned-scroll-item">
                                <?php if ($item['label']) : ?>
                                    <span class="c--pinned-scroll-a__badge js--pinned-scroll-line"><?= esc_html($item['label']) ?></span>
                                <?php endif; ?>
                                <span class="c--pinned-scroll-a__dash js--pinned-scroll-dash"></span>
                                <?php if ($item['title']) : ?>
                                    <h2 class="c--pinned-scroll-a__title js--pinned-scroll-line"><?= esc_html($item['title']) ?></h2>
                                <?php endif; ?>
                                <?php if ($item['text']) : ?>
                                    <p class="c--pinned-scroll-a__content js--pinned-scroll-line"><?= esc_html($item['text']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($items);
?>
