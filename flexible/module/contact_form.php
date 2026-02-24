<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $title = $module['title'] ?? '';
    $description = $module['description'] ?? '';
    $pill = $module['pill'] ?? '';
    $form_shortcode = $module['contact_form_id'] ?? '';
?>
<section class="module_contact_form <?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12 f--col-tabletl-5">
                <?php if ($title): ?>
                    <h2 class="f--font-a u--mb-4"><?= $title ?></h2>
                <?php endif; ?>
                <?php if ($description): ?>
                    <p class="f--font-e u--mb-4"><?= $description ?></p>
                <?php endif; ?>
                <?php if ($pill): ?>
                    <span class="c--pill-a"><?= $pill ?></span>
                <?php endif; ?>
            </div>
            <div class="f--col-12 f--col-tabletl-6 f--offset-tabletl-1">
                <?php if ($form_shortcode): ?>
                    <?= do_shortcode($form_shortcode) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($title);
unset($description);
unset($pill);
unset($form_shortcode);
?>
