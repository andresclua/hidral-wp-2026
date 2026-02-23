<section class="module_list_of <?= $spacingContent = get_spacing(($module['section_spacing'])); ?>">
    <div class="f--container">
        <div class="f--row f--gap-a">
        <?php
            if ($module['productos'] === 'manual' && !empty($module['manual_selection'])) {
                $listOfQuery = $module['manual_selection'];
                foreach ($listOfQuery as $post) {
                    setup_postdata($post);
                    ?>
                    <div class="f--col-3">
                        <?php include(locate_template('components/card/card-a.php', false, false)); ?>
                    </div>
                    <?php
                }
                wp_reset_postdata();
            } else {
                $args = array(
                    'post_type'      => $module['productos'],
                    'posts_per_page' => -1,
                );
                $listOfQuery = new WP_Query($args);
                if ($listOfQuery->have_posts()) {
                    while ($listOfQuery->have_posts()) {
                        $listOfQuery->the_post();
                        ?>
                        <div class="f--col-3">
                            <?php include(locate_template('components/card/card-a.php', false, false)); ?>
                        </div>
                        <?php
                    }
                }
                wp_reset_postdata();
            }
        ?>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
?>