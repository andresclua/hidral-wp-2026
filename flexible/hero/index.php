<?php
switch ($hero['acf_fc_layout']) {
    case 'hero_transparente':
        include (locate_template('flexible/hero/hero_transparente.php', false, false));
        break;
    case 'hero_blanca':
        include (locate_template('flexible/hero/hero_blanca.php', false, false));
        break;
    case 'hero_a':
        include (locate_template('flexible/hero/hero_a.php', false, false));
        break;
    case 'hero_b':
        include (locate_template('flexible/hero/hero_b.php', false, false));
        break;
}