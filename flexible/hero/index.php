<?php

switch ($hero['acf_fc_layout']) {
    case 'simple_hero':
        include get_template_directory() . '/flexible/hero/simple_hero.php';
        break;
    case 'media_hero':
        include get_template_directory() . '/flexible/hero/media_hero.php';
        break;
    case 'centered_hero':
        include get_template_directory() . '/flexible/hero/centered_hero.php';
        break;
}