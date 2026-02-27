<?php
    $spacingContent = get_spacing(($module['section_spacing']));
?>
<section class="module_global_cta c--cta-b <?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12">
                <?php include(locate_template('components/cta/cta-b.php', false, false)); ?>
            </div>
        </div>
    </div>
</section>
<?php unset($spacingContent); ?>
