<?php 
    if(is_preview()){
        $highlighted = $block['data']['highlighted'];
    }else{
        $highlighted = get_field('highlighted');
    }

    include(locate_template('components/highlighted/highlighted.php', false, false)); 
?>
   