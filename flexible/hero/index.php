<?php
switch ($hero['acf_fc_layout']) {
    case 'hero_a':
        include (locate_template('flexible/hero/hero_a.php', false, false));
        break;
}
