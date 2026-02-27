<?php
switch ($module['acf_fc_layout']) {
    case 'media':
        include (locate_template('flexible/module/media.php', false, false));
        break;
    case 'content_and_button':
        include (locate_template('flexible/module/content_and_button.php', false, false));
        break;
    case 'short_content':
        include (locate_template('flexible/module/short_content.php', false, false));
        break;
    case 'list_of':
        include (locate_template('flexible/module/list_of.php', false, false));
        break;
    case 'CTA':
        include (locate_template('flexible/module/CTA.php', false, false));
        break;
    case 'FAQ':
        include (locate_template('flexible/module/FAQ.php', false, false));
        break;
    case 'Global_Marquee_Text':
        include (locate_template('flexible/module/global_text_marquee.php', false, false));
        break;
    case 'Global_Marquee_Asset':
        include (locate_template('flexible/module/global_asset_marquee.php', false, false));
        break;
    case 'text_and_image':
        include (locate_template('flexible/module/text_and_image.php', false, false));
        break;
    case 'Gallery_Grid':
        include (locate_template('flexible/module/gallery_grid.php', false, false));
        break;
    case 'content':
        include (locate_template('flexible/module/content.php', false, false));
        break;
case 'map':
        include (locate_template('flexible/module/map.php', false, false));
        break;
    case 'contact':
        include (locate_template('flexible/module/contact_form.php', false, false));
        break;
    case 'Stats':
        include (locate_template('flexible/module/stats.php', false, false));
        break;
    case 'Global_CTA':
        include (locate_template('flexible/module/global_cta.php', false, false));
        break;
}
