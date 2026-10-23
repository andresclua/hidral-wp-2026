<?php
$title    = $module['title'] ?? '';
$subtitle = $module['subtitle'] ?? '';
$listType = $module['list_type'] ?? '';
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');
$bgColor = in_array($listType, array('card_with_image', 'card_with_icon', 'card_simple'), true)
    ? get_bg_color($module['bg_color'] ?? '')
    : null;
$textColorClass = $bgColor ? ($bgColor['isLight'] ? 'f--color-a' : 'f--color-b') : '';
$itemsPerRow = intval($module['items_per_row'] ?? 4) === 3 ? 3 : 4;
// 1 per row on mobile, 2 on tablet, items_per_row (3 or 4) from tabletl up
$colClass = 'f--col-12 f--col-tabletm-6 f--col-tabletl-' . (12 / $itemsPerRow);
$textAlignClass = 'f--text-' . ($module['text_align'] ?? 'left');
$alignItemsClass = 'f--align-items-' . (($module['text_align'] ?? 'left') === 'center' ? 'center' : 'flex-start');

if (!$title && !$subtitle && !$listType) {
    return;
}
?>
<section>
    <div
        class="c--list-of-a <?= esc_attr($textColorClass) ?> <?= $spacingContentModule ?>"
        anchor-id="module-<?= $keyIndexModule ?>"
        <?php if ($bgColor) : ?>
            style="background: <?= esc_attr($bgColor['hex']) ?>;"
        <?php endif; ?>
    >
        <div class="f--container">
            <?php if ($title || $subtitle) : ?>
                <div class="f--row">
                    <div class="f--col-12">
                        <div class="c--list-of-a__hd js--reveal-it" data-reveal-from="bottom">
                            <?php if ($title) : ?>
                                <h2 class="c--list-of-a__title"><?= esc_html($title) ?></h2>
                            <?php endif; ?>
                            <?php if ($subtitle) : ?>
                                <p class="c--list-of-a__subtitle"><?= esc_html($subtitle) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($listType === 'services') :
                $services = $module['services'] ?? [];
                if ($services) : ?>
                    <div class="f--row c--list-of-a__list-group js--reveal-stack" data-reveal-from="bottom">
                        <?php foreach ($services as $service) :
                            $serviceId = is_object($service) ? $service->ID : $service;
                            $card = array(
                                'category'    => get_field('service_category', $serviceId),
                                'title'       => get_the_title($serviceId),
                                'description' => get_field('card_description', $serviceId) ?: get_the_excerpt($serviceId),
                                'link'        => get_permalink($serviceId),
                                'image'       => get_post_thumbnail_id($serviceId),
                            );
                        ?>
                            <div class="<?= $colClass ?> <?= $textAlignClass ?>">
                                <?php include(locate_template('components/card/card-a.php', false, false)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif;
            elseif ($listType === 'card_with_image') :
                $itemsImage = $module['items_image'] ?? [];
                if ($itemsImage) : ?>
                    <div class="f--row c--list-of-a__list-group js--reveal-stack" data-reveal-from="bottom">
                        <?php foreach ($itemsImage as $item) :
                            $card = array(
                                'title'       => $item['title'] ?? '',
                                'description' => $item['description'] ?? '',
                                'image'       => $item['image'] ?? null,
                            );
                        ?>
                            <div class="<?= $colClass ?> <?= $textAlignClass ?>">
                                <?php include(locate_template('components/card/card-a.php', false, false)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif;
            elseif ($listType === 'card_with_icon') :
                $itemsIcon = $module['items_icon'] ?? [];
                if ($itemsIcon) : ?>
                    <div class="f--row c--list-of-a__list-group js--reveal-stack" data-reveal-from="bottom">
                        <?php foreach ($itemsIcon as $item) :
                            $card = array(
                                'title'       => $item['title'] ?? '',
                                'description' => $item['description'] ?? '',
                                'image'       => $item['image'] ?? null,
                                'modifier'    => trim($alignItemsClass . ' ' . ($bgColor && !$bgColor['isLight'] ? 'c--card-b--second' : '')),
                            );
                        ?>
                            <div class="<?= $colClass ?> <?= $textAlignClass ?>">
                                <?php include(locate_template('components/card/card-b.php', false, false)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif;
            elseif ($listType === 'card_simple') :
                $itemsSimple = $module['items_simple'] ?? [];
                if ($itemsSimple) : ?>
                    <div class="f--row c--list-of-a__list-group js--reveal-stack" data-reveal-from="bottom">
                        <?php foreach ($itemsSimple as $item) :
                            $card = array(
                                'title'       => $item['title'] ?? '',
                                'description' => $item['subtitle'] ?? '',
                                'modifier'    => trim('c--card-b--third ' . $alignItemsClass . ' ' . ($bgColor && !$bgColor['isLight'] ? 'c--card-b--second' : '')),
                            );
                        ?>
                            <div class="<?= $colClass ?> <?= $textAlignClass ?>">
                                <?php include(locate_template('components/card/card-b.php', false, false)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif;
            endif; ?>

            <?php
            $buttons = $module['buttons'] ?? [];
            $btnModifier = $bgColor && !$bgColor['isLight'] ? 'c--btn-a--third' : '';
            if ($buttons) : ?>
                <div class="c--list-of-a__wrapper js--reveal-it" data-reveal-from="bottom">
                    <?php foreach ($buttons as $i => $buttonRow) :
                        $modifier = $i === 0 ? $btnModifier : 'c--btn-a--second';
                        render_wp_button(array(
                            'button' => $buttonRow['button'],
                            'class'  => trim('c--btn-a ' . $modifier),
                        ));
                    endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
unset($title);
unset($subtitle);
unset($listType);
unset($spacingContentModule);
unset($bgColor);
unset($textColorClass);
unset($itemsPerRow);
unset($colClass);
unset($textAlignClass);
unset($alignItemsClass);
unset($buttons);
unset($btnModifier);
?>
