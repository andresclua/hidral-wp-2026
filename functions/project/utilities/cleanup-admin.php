<?php

// Remove Posts, Comments and General Options from admin menu
add_action('admin_menu', function () {
    remove_menu_page('edit.php');           // Posts
    remove_menu_page('edit-comments.php');  // Comments
    remove_menu_page('general-options');    // General Options (legacy)
}, 999);

// Remove Comments from admin bar
add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}, 999);

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);
