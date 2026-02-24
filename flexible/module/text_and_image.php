<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $title = $module['title'] ?? '';
    $content = $module['content'] ?? '';
    $image = $module['image'] ?? '';
    $imageLeft = ($keyIndexModule % 2 === 0);
?>
<section class="text_and_image <?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12 f--col-wide-10 f--offset-wide-1">
                <div class="f--row u--align-items-center">
                    <div class="f--col-12 f--col-tabletl-5">
                        <?php if ($imageLeft): ?>
                                <?php render_wp_image([
                                'image' => $image,
                                'sizes' => '(max-width: 1024px) 100vw, 50vw',
                                'class' => 'c--media-b js--parallax-background' ,
                                'isLazy' => true,
                                'showAspectRatio' => true,
                            ]); ?>
                            
                        <?php else: ?>
                            <?php if ($title): ?>
                                <h2 class="f--font-c u--mb-2"><?= $title ?></h2>
                            <?php endif; ?>
                            <?php if ($content): ?>
                                <div class="c--content-a u--mb-2"><?= $content ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="f--col-12 f--col-tabletl-5 f--offset-tabletl-1">
                        <?php if ($imageLeft): ?>
                            <?php if ($title): ?>
                                <h2 class="f--font-c u--mb-2"><?= $title ?></h2>
                            <?php endif; ?>
                            <?php if ($content): ?>
                                <div class="c--content-a u--mb-2"><?= $content ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php render_wp_image([
                                'image' => $image,
                                'sizes' => '(max-width: 1024px) 100vw, 50vw',
                                'class' => 'c--media-b js--parallax-background' ,
                                'isLazy' => true,
                                'showAspectRatio' => true,
                            ]); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($title);
unset($content);
unset($image);
unset($imageLeft);
?>
