<?php 
    $spacingMedia = get_spacing(($module['section_spacing'])); 
    $imageUrl = is_array($module['image']) && isset($module['image']['url']) ? $module['image']['url'] : false;
?>
<?php if ($imageUrl) : ?>
    <section class="module_media <?= $spacingMedia ?> js--parallax-background" style="background-image: url('<?= $imageUrl ?>');">
   
</section>
<?php endif; ?>
<?php
unset($spacingMedia);
unset($imageUrl);
?>
