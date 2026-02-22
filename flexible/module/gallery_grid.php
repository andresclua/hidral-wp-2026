<?php 
    $spacingContent = get_spacing(($module['section_spacing'])); 
    $gallery = $module['gallery'];
    // $description = $module['description'];
    // $faqItems = $module['faq_item'];

?>
<section class="module_gallery_grid  <?= $spacingContent = get_spacing(($module['section_spacing'])); ?>">
    <div class="f--container">
        <div class="f--row">
            <?php if ($gallery): ?>
                <?php foreach ($gallery as $index => $item): ?>
                <div class="f--col-4">
                    <div class="c--card-b">
                        <div class="c--card-b__wrapper" style="background-image: url('<?php echo $item['image']['url'] ?>');"></div>
                        <p class="c--card-b__title"><?= $item['title'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
?>