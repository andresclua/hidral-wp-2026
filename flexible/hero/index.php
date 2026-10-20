<?php

switch ($hero['acf_fc_layout']) {
    case 'centered_hero':
        include get_template_directory() . '/flexible/hero/centered_hero.php';
        break;
}