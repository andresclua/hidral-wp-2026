<?php 
/**
 * Customizes the WordPress login page logo.
 *
 * This function replaces the default WordPress login logo with a custom image
 * located in the theme's "img/logo/logo.webp". It sets the logo's size and appearance.
 *
 * @author Eli
 */
function wp_login_logo()
{ ?>
    <style type="text/css">
        #login h1 a,
        .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/img/logo/logo.webp);
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
?>