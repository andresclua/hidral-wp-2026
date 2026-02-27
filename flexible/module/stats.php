<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $stats = $module['stat'] ?? [];
?>
<section class="module_stats <?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row f--gap-a">
            <?php foreach ($stats as $stat):
                $stat_number = $stat['value'];
                $stat_title = $stat['title'];
                $stat_description = $stat['description'];
            ?>
                <div class="f--col-12 f--col-tabletm-4 f--col-tabletl-4">
                    <?php include(locate_template('components/card/card-b.php', false, false)); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php unset($spacingContent, $stats, $stat_number, $stat_title, $stat_description); ?>
