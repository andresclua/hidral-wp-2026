<?php
    if(is_preview()){
        if($block['data']){
            $icon_title = $block['data']['icon_title'];
            $image_option = $block['data']['image_option'];
        }else{
            $icon_title = get_field('icon_title');
            $image_option = get_field('image_option');
        }
       
    }else{
        $icon_title = get_field('icon_title');
        $image_option = get_field('image_option');
    }
    ?>

    <?php if($icon_title) : ?>
        <div>    
            <h2 class="c--icon-heading-a__title"><?= $icon_title ?></h2>
        </div>
    <?php endif;?>