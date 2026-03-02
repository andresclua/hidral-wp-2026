<?php
    $spacingContent = get_spacing(($module['section_spacing']));
?>
<section class="module_global_cta <?= $spacingContent ?>">
    <?php include(locate_template('components/cta/cta-b.php', false, false)); ?>
</section>
<?php unset($spacingContent); ?>
