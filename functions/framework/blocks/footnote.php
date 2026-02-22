<?php 
    if(is_preview()){
        $footnote = $block['data']['footnote'];
    }else{
        $footnote = get_field('footnote');
    }
    include(locate_template('components/footnote/footnote.php', false, false)); 
?>
