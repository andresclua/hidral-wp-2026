<?php
$keyIndexModule = $keyIndexModule + 1;

switch ($module['acf_fc_layout']) {
    case 'accordion':
        include get_template_directory() . '/flexible/module/accordion.php';
        break;
    case 'button_options':
        include get_template_directory() . '/flexible/module/button_options.php';
        break;
    case 'footnote':
        include get_template_directory() . '/flexible/module/footnote.php';
        break;
    case 'media_options':
        include get_template_directory() . '/flexible/module/media_options.php';
        break;
    case 'sidenav':
        include get_template_directory() . '/flexible/module/sidenav.php';
        break;
}
