<?php
$doc_url  = admin_url('admin.php?page=terra_client_documentation');
$sys_url  = admin_url('admin.php?page=terra_dashboard');

return array(
    'cards' => array(
        array(
            'title'       => 'General Options',
            'description' => 'Site identity, header, footer, and social media settings.',
            'url'         => admin_url('admin.php?page=general-options'),
            'button'      => 'Configure',
            'column'      => 'normal',
            'icon'        => 'dashicons-admin-generic',
            'details'     => function () {
                $config_path = get_template_directory() . '/functions/project/config/general-options/index.php';
                if (!file_exists($config_path)) return;
                $config = include $config_path;
                $groups = isset($config['groups']) ? $config['groups'] : array();
                $base = admin_url('admin.php?page=general-options');
                echo '<ul>';
                foreach ($groups as $slug => $group) {
                    $label = isset($group['title']) ? $group['title'] : ucwords(str_replace('_', ' ', $slug));
                    $tab_slug = str_replace('_', '-', $slug);
                    echo '<li><a href="' . esc_url($base) . '&tab=' . esc_attr($tab_slug) . '">' . esc_html($label) . '</a></li>';
                }
                echo '</ul>';
            },
        ),
        array(
            'title'       => 'System',
            'description' => 'Framework modules, URL health checks, and monitoring tools.',
            'url'         => $sys_url,
            'button'      => 'View Status',
            'column'      => 'side',
            'icon'        => 'dashicons-shield',
            'details'     => function () use ($sys_url) {
                $interval_map = array('86400' => 'Every day', '604800' => 'Every week', '2592000' => 'Every month');
                $current_interval = get_option('terra_sw_interval', '604800');
                $interval_label = isset($interval_map[$current_interval]) ? $interval_map[$current_interval] : 'Periodically';

                $emails = get_option('terra_sw_emails', array());
                $emails = array_filter($emails);
                $emails_label = !empty($emails) ? implode(', ', array_map('esc_html', $emails)) : 'No recipients configured';

                $active_modules = get_option('terra_active_modules', array());
                $total_active = !empty($active_modules) ? count(array_filter($active_modules)) : 'All (default)';

                $tabs = array(
                    'Modules'             => 'modules',
                    'Settings'            => 'settings',
                    'Google Search Console'=> 'gsc',
                    'URL Health Check'    => 'url-health',
                );

                echo '<ul>';
                echo '<li><strong>URL Health Check:</strong> ' . esc_html($interval_label) . '</li>';
                echo '<li><strong>Reports to:</strong> ' . $emails_label . '</li>';
                echo '<li><strong>Active modules:</strong> ' . esc_html($total_active) . '</li>';
                echo '</ul>';
                echo '<p style="margin-top:8px;"><strong>Sections:</strong></p>';
                echo '<ul>';
                foreach ($tabs as $label => $slug) {
                    echo '<li><a href="' . esc_url($sys_url) . '&tab=' . esc_attr($slug) . '">' . esc_html($label) . '</a></li>';
                }
                echo '</ul>';
            },
        ),
        array(
            'title'       => 'Client Documentation',
            'description' => 'Auto-generated documentation for content types, taxonomies, and modules.',
            'url'         => $doc_url,
            'button'      => 'Read Docs',
            'column'      => 'column3',
            'icon'        => 'dashicons-book-alt',
            'details'     => function () use ($doc_url) {
                // Base categories always present
                $categories = array(
                    'general' => 'General',
                    'design'  => 'Design System',
                );

                // Dynamically add categories based on project config
                $config_path = get_template_directory() . '/functions/project/config/index.php';
                if (file_exists($config_path)) {
                    $config = include $config_path;
                    if (!empty($config['post_types'])) {
                        $categories['post_types'] = 'Content Types';
                    }
                    if (!empty($config['taxonomies'])) {
                        $categories['taxonomies'] = 'Taxonomies';
                    }
                    if (!empty($config['flexible_modules']['layouts']) || !empty($config['flexible_heros']['layouts'])) {
                        $categories['modules'] = 'Modules';
                    }
                    if (!empty($config['general_options']['groups'])) {
                        $categories['options'] = 'Settings';
                    }
                }

                // Maintain consistent order
                $order = array('general', 'post_types', 'taxonomies', 'modules', 'options', 'design');
                echo '<ul>';
                foreach ($order as $slug) {
                    if (!isset($categories[$slug])) continue;
                    echo '<li><a href="' . esc_url($doc_url) . '&tab=' . esc_attr($slug) . '">' . esc_html($categories[$slug]) . '</a></li>';
                }
                echo '</ul>';
            },
        ),
        array(
            'title'       => 'Create New',
            'description' => 'Quick access to create new content.',
            'url'         => admin_url('edit.php?post_type=page'),
            'button'      => 'All Pages',
            'column'      => 'normal',
            'icon'        => 'dashicons-plus-alt',
            'details'     => function () {
                $items = array(
                    array('label' => 'Page', 'url' => admin_url('post-new.php?post_type=page'), 'icon' => 'dashicons-admin-page'),
                );

                $config_path = get_template_directory() . '/functions/project/config/post-types_config.php';
                if (file_exists($config_path)) {
                    $post_types = include $config_path;
                    foreach ($post_types as $pt) {
                        $icon = isset($pt['args']['menu_icon']) ? $pt['args']['menu_icon'] : 'dashicons-admin-post';
                        $items[] = array(
                            'label' => $pt['singular_name'],
                            'url'   => admin_url('post-new.php?post_type=' . $pt['post_type']),
                            'icon'  => $icon,
                        );
                    }
                }

                echo '<div class="terra-dashboard-widget__actions">';
                foreach ($items as $item) {
                    echo '<a href="' . esc_url($item['url']) . '" class="terra-dashboard-widget__action">';
                    echo '<span class="dashicons ' . esc_attr($item['icon']) . '"></span>';
                    echo esc_html($item['label']);
                    echo '</a>';
                }
                echo '</div>';
            },
        ),
        array(
            'title'       => 'Recent Activity',
            'description' => 'Latest content changes across the site.',
            'url'         => '',
            'button'      => '',
            'column'      => 'side',
            'icon'        => 'dashicons-clock',
            'details'     => function () {
                $recent = get_posts(array(
                    'post_type'   => 'any',
                    'post_status' => array('publish', 'draft', 'pending'),
                    'numberposts' => 8,
                    'orderby'     => 'modified',
                    'order'       => 'DESC',
                ));

                if (empty($recent)) {
                    echo '<p class="description">No recent activity.</p>';
                    return;
                }

                echo '<table class="widefat striped">';
                echo '<thead><tr><th>Title</th><th>Type</th><th>Modified</th><th></th></tr></thead><tbody>';
                foreach ($recent as $post) {
                    $type_obj = get_post_type_object($post->post_type);
                    $type_label = $type_obj ? $type_obj->labels->singular_name : $post->post_type;
                    $status = $post->post_status !== 'publish' ? ' <span class="terra-dashboard-widget__badge">' . esc_html(ucfirst($post->post_status)) . '</span>' : '';
                    $time_ago = human_time_diff(strtotime($post->post_modified), current_time('timestamp'));

                    echo '<tr>';
                    echo '<td>' . esc_html($post->post_title) . $status . '</td>';
                    echo '<td>' . esc_html($type_label) . '</td>';
                    echo '<td>' . esc_html($time_ago) . ' ago</td>';
                    echo '<td><a href="' . esc_url(get_edit_post_link($post->ID)) . '" class="button button-small">Edit</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            },
        ),
        array(
            'title'       => 'Media Assets',
            'description' => '',
            'url'         => admin_url('upload.php'),
            'button'      => 'Media Library',
            'column'      => 'column3',
            'icon'        => 'dashicons-images-alt2',
            'details'     => function () {
                $upload_dir = wp_upload_dir();
                $total_count = wp_count_posts('attachment')->inherit;

                // Get total uploads size
                $dir_size = 0;
                $base_path = $upload_dir['basedir'];
                if (is_dir($base_path)) {
                    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_path, RecursiveDirectoryIterator::SKIP_DOTS));
                    foreach ($iter as $file) {
                        $dir_size += $file->getSize();
                    }
                }

                echo '<p class="description" style="margin:0 0 12px;">Overview of uploaded media files &mdash; <strong>' . esc_html(number_format($total_count)) . '</strong> files, <strong>' . esc_html(size_format($dir_size)) . '</strong> total</p>';

                // All attachments with file size
                $all = get_posts(array(
                    'post_type'   => 'attachment',
                    'post_status' => 'inherit',
                    'numberposts' => 50,
                    'orderby'     => 'date',
                    'order'       => 'DESC',
                ));

                echo '<div class="terra-dashboard-widget__scroll">';
                echo '<table class="widefat striped terra-sortable">';
                echo '<thead><tr>';
                echo '<th data-sort="string" class="terra-sortable__th">File <span class="dashicons dashicons-sort"></span></th>';
                echo '<th data-sort="string" class="terra-sortable__th">Type <span class="dashicons dashicons-sort"></span></th>';
                echo '<th data-sort="number" class="terra-sortable__th">Size <span class="dashicons dashicons-sort"></span></th>';
                echo '<th data-sort="number" class="terra-sortable__th">Date <span class="dashicons dashicons-sort"></span></th>';
                echo '</tr></thead><tbody>';

                foreach ($all as $att) {
                    $path = get_attached_file($att->ID);
                    $size = $path && file_exists($path) ? filesize($path) : 0;
                    $ext = strtoupper(pathinfo($att->guid, PATHINFO_EXTENSION));
                    $date = get_the_date('j M Y', $att->ID);
                    $timestamp = get_the_date('U', $att->ID);

                    echo '<tr>';
                    echo '<td><a href="' . esc_url(get_edit_post_link($att->ID)) . '">' . esc_html(wp_trim_words($att->post_title, 5)) . '</a></td>';
                    echo '<td>' . esc_html($ext) . '</td>';
                    echo '<td data-value="' . esc_attr($size) . '">' . esc_html(size_format($size)) . '</td>';
                    echo '<td data-value="' . esc_attr($timestamp) . '">' . esc_html($date) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
                echo '</div>';

                // Sort JS
                echo '<script>'
                    . '(function(){'
                    . 'document.querySelectorAll(".terra-sortable").forEach(function(table){'
                    . 'var headers=table.querySelectorAll("th[data-sort]");'
                    . 'headers.forEach(function(th,colIdx){'
                    . 'th.style.cursor="pointer";'
                    . 'var asc=true;'
                    . 'th.addEventListener("click",function(){'
                    . 'var tbody=table.querySelector("tbody");'
                    . 'var rows=Array.from(tbody.querySelectorAll("tr"));'
                    . 'var type=th.getAttribute("data-sort");'
                    . 'rows.sort(function(a,b){'
                    . 'var aCell=a.cells[colIdx],bCell=b.cells[colIdx];'
                    . 'var aVal=aCell.getAttribute("data-value")||aCell.textContent.trim();'
                    . 'var bVal=bCell.getAttribute("data-value")||bCell.textContent.trim();'
                    . 'if(type==="number"){aVal=parseFloat(aVal)||0;bVal=parseFloat(bVal)||0;return asc?aVal-bVal:bVal-aVal}'
                    . 'return asc?aVal.localeCompare(bVal):bVal.localeCompare(aVal)'
                    . '});'
                    . 'rows.forEach(function(r){tbody.appendChild(r)});'
                    . 'headers.forEach(function(h){h.classList.remove("sort-asc","sort-desc")});'
                    . 'th.classList.add(asc?"sort-asc":"sort-desc");'
                    . 'asc=!asc'
                    . '})})})})();'
                    . '</script>';
            },
        ),
    ),
);
