<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $title = $module['title'];
    $link = $module['link'];
?>
<section class="module_CTA c--cta-a <?= $spacingContent ?>">
    <div class="f--container">
       <div class="f--row">
            <div class="f--col-12">
                 <?php include(locate_template('components/cta/cta-a.php', false, false)); ?>
            </div>
       </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($title);
unset($link);
?>
