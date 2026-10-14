<?php
$pretitle = $module['pretitle'] ?? '';
$title    = $module['title'] ?? '';
$text     = $module['text'] ?? '';
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');

if (!$pretitle && !$title && !$text && empty($module['image'])) {
    return;
}

$media = array(
    'type'   => 'image',
    'image'  => $module['image'] ?? null,
    'sizes'  => 'large',
    'isLazy' => true,
);
?>
<section>
    <div
        class="c--media-text-a <?= $spacingContentModule ?>"
        anchor-id="module-<?= $keyIndexModule ?>"
    >
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-12 f--col-tabletm-8 f--offset-tabletm-2">
                    <div class="f--row">
                        <div class="f--col-12 f--col-tabletm-6">
                            <?php
                            $card = array(
                                'category' => $pretitle,
                                'title'    => $title,
                                'content'  => $text,
                                'button'   => $module['custom_button'] ?? null,
                            );
                            include(locate_template('components/card/card-d.php', false, false));
                            ?>
                        </div>
                        <div class="f--col-12 f--col-tabletm-6">
                            <?php include(locate_template('components/media/core-media.php', false, false)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($pretitle);
unset($title);
unset($text);
unset($spacingContentModule);
unset($media);
?>
