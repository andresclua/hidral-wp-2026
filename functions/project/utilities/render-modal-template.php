<?php
/**
 * Render a modal-content PHP component server-side and return its HTML.
 *
 * Shared by the `modal_content` AJAX action (ajax variation — fetched on open)
 * and core-btn's htmlContent variation (inlined into data-modal-content at page
 * render time). The only difference between the two variations is *when* this
 * runs: lazily over AJAX, or upfront inline.
 *
 * Resolves the template safely (must be a .php file inside the theme; realpath
 * blocks ../ traversal), sets up the post ($modal_id + global $post) and returns
 * the captured markup, or '' on any failure.
 *
 * @param string $template Theme-relative path, e.g. 'components/card/card-x.php'.
 * @param int    $id       Post ID whose data the template renders.
 * @return string
 */
function terra_render_modal_template($template, $id) {
    $template = (string) $template;
    $id       = (int) $id;

    $theme_dir     = realpath(get_template_directory());
    $template_path = realpath(get_template_directory() . '/' . ltrim($template, '/'));

    $is_valid = $template_path
        && $theme_dir
        && strpos($template_path, $theme_dir . DIRECTORY_SEPARATOR) === 0
        && substr($template_path, -4) === '.php';

    if (!$is_valid) {
        return '';
    }

    global $post;
    $post = get_post($id);
    if (!$post || $post->post_status !== 'publish') {
        return '';
    }
    setup_postdata($post);
    $modal_id = $id;

    ob_start();
    include $template_path;
    $rendered = ob_get_clean();

    wp_reset_postdata();

    return $rendered;
}
