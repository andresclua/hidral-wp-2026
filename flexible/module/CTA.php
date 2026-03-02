<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $title = $module['title'];
    $link = $module['link'];
    $cta_image = $module['image'] ?? null;
    $bg_url = is_array($cta_image) ? $cta_image['url'] : $cta_image;
?>
<section class="module_CTA <?= $spacingContent ?>">
    <?php include(locate_template('components/cta/cta-a.php', false, false)); ?>
</section>
<?php
unset($spacingContent);
unset($title);
unset($link);
unset($cta_image);
unset($bg_url);
?>
