<?php 
    $spacingContent = get_spacing(($module['section_spacing'])); 

?>
<section class="module_global_text_marquee  <?= $spacingContent = get_spacing(($module['section_spacing'])); ?>">
<ul class="c--global-marquee-a js--marquee" data-speed="1" data-reversed="false">
        <?php for ($i = 0; $i < 10; $i++): ?>
            <li class="c--global-marquee-a__item">
                <?php the_field('marquee_banner_text', 'option'); ?>
            </li>
        <?php endfor; ?>
    </ul>
</section>
<?php
unset($spacingContent);
?>