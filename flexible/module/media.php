<?php 
    $spacingMedia = get_spacing(($module['section_spacing'])); 
    $imageUrl = is_array($module['image']) && isset($module['image']['url']) ? $module['image']['url'] : false;
?>
<?php if ($imageUrl) : ?>

<section class="module_media <?= $spacingMedia ?>">
   <div class="f--container">
        <div class="f--row">
            <div class="f--col-12">
                <div class="c--media-a js--parallax-background"  style="background-image: url('<?= $imageUrl ?>');" >

                </div>
            </div>
        </div>
   </div>
</section>
<?php endif; ?>
<?php
unset($spacingMedia);
unset($imageUrl);
?>
