<?php
$keyIndexModule = $keyIndexModule + 1;

switch ($module['acf_fc_layout']) {
    case 'contact':
        include get_template_directory() . '/flexible/module/contact.php';
        break;
    case 'list_of':
        include get_template_directory() . '/flexible/module/list_of.php';
        break;
    case 'media_text':
        include get_template_directory() . '/flexible/module/media_text.php';
        break;
    case 'timeline':
        include get_template_directory() . '/flexible/module/timeline.php';
        break;
    case 'trusted_companies':
        include get_template_directory() . '/flexible/module/trusted_companies.php';
        break;
}
