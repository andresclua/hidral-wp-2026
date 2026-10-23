<?php
$title = $module['title'] ?? '';
$items = $module['items'] ?? [];
$buttons = $module['buttons'] ?? [];
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');

if (!$title && !$items) {
    return;
}
?>
<section>
    <div
        class="c--timeline-a <?= $spacingContentModule ?>"
        anchor-id="module-<?= $keyIndexModule ?>"
    >
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-12 f--col-tabletm-8 f--offset-tabletm-2">
                    <div class="f--row">
                        <div class="f--col-12 f--col-tabletm-4 js--reveal-it" data-reveal-from="bottom">
                            <?php if ($title) : ?>
                                <h2 class="c--timeline-a__title"><?= esc_html($title) ?></h2>
                            <?php endif; ?>
                            <?php if ($buttons) : ?>
                                <div class="c--timeline-a__wrapper">
                                    <?php foreach ($buttons as $i => $buttonRow) :
                                        $modifier = $i === 0 ? '' : 'c--btn-a--second';
                                        render_wp_button(array(
                                            'button' => $buttonRow['button'],
                                            'class'  => trim('c--btn-a ' . $modifier),
                                        ));
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($items) : ?>
                            <div class="f--col-12 f--col-tabletm-8">
                                <div class="c--timeline-a__list-group js--reveal-stack" data-reveal-from="bottom">
                                    <?php foreach ($items as $i => $item) :
                                        $card = array(
                                            'title'       => $item['title'] ?? '',
                                            'description' => $item['description'] ?? '',
                                        );
                                    ?>
                                        <div class="c--timeline-a__item">
                                            <?php include(locate_template('components/card/card-c.php', false, false)); ?>
                                            <?php if ($i < count($items) - 1) : ?>
                                                <span class="c--timeline-a__dash"></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($title);
unset($items);
unset($buttons);
unset($spacingContentModule);
?>
