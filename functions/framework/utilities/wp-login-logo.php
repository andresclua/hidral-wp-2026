<?php
/**
 * Customizes the WordPress login page logo.
 *
 * Uses the 'login_logo' field from General Options (ACF).
 * Falls back to a Terra placeholder if not defined.
 */
function wp_login_logo() {
    $logo_url = '';

    if (function_exists('get_field')) {
        $login_logo = get_field('login_logo', 'option');
        if (!empty($login_logo['url'])) {
            $logo_url = $login_logo['url'];
        }
    }

    if (empty($logo_url)) {
        $logo_url = 'http://placeholder.terrahq.com/logo-rectangular.webp';
    }
    ?>
    <style type="text/css">
        #login h1 a,
        .login h1 a {
            background-image: url(<?php echo esc_url($logo_url); ?>);
            height: 80px;
            width: 235px;
            background-repeat: no-repeat;
            padding-bottom: 30px;
            background-size: contain;
            margin-bottom: 0;
            padding: 0;
        }
    </style>
<?php }
add_action('login_enqueue_scripts', 'wp_login_logo');
