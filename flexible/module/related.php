<section class="module_related <?= $spacingContent = get_spacing(($module['section_spacing'])); ?>">
    <div class="f--container">
        <div class="f--row">
        <?php
            $relatedPosts = $module['relationship'];
            if ($relatedPosts):
                foreach ($relatedPosts as $post):
                    setup_postdata($post);
                    ?>
                    <div class="f--col-3">
                        <?php include(locate_template('components/card/card-a.php', false, false)); ?>
                    </div>
                    <?php
                endforeach;
                wp_reset_postdata();
            endif;
        ?>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
?>