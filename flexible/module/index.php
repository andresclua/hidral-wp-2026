<?php
$keyIndexModule = $keyIndexModule + 1;

switch ($module['acf_fc_layout']) {
    case 'accordion':
        include get_template_directory() . '/flexible/module/accordion.php';
        break;
    case 'button_options':
        include get_template_directory() . '/flexible/module/button_options.php';
        break;
    case 'contact':
        include get_template_directory() . '/flexible/module/contact.php';
        break;
    case 'feature_list':
        include get_template_directory() . '/flexible/module/feature_list.php';
        break;
    case 'footnote':
        include get_template_directory() . '/flexible/module/footnote.php';
        break;
    case 'media_options':
        include get_template_directory() . '/flexible/module/media_options.php';
        break;
    case 'services_list':
        include get_template_directory() . '/flexible/module/services_list.php';
        break;
    case 'trusted_companies':
        include get_template_directory() . '/flexible/module/trusted_companies.php';
        break;
}
