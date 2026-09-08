<?php

function filtered_content($search_name) {
    $flexible_field = get_field($search_name);
    $field_object = get_field_object($search_name);
    $current_page_title = get_the_title();
    $found_names = [];

    if ($flexible_field) {
        foreach ($flexible_field as $module) {
            if (!empty($module) && $module['acf_fc_layout']) {
                $module_id = $module['acf_fc_layout'];
                $module_name = isset($field_object['layouts'][$module_id]['label'])
                    ? $field_object['layouts'][$module_id]['label']
                    : $module_id;

                $found_names[$module_name][$current_page_title."-".get_the_ID()] = '✔️';
            }
        }
    }

    return $found_names;
}

function show_custom_search_module() {
    $post_types = get_post_types(['public' => true], 'names');
    $found_modules = [];
    $found_heros = [];

    foreach ($post_types as $post_type) {
        $query = new WP_Query([
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $found_heros = array_merge_recursive($found_heros, filtered_content('heros'));
                $found_modules = array_merge_recursive($found_modules, filtered_content('modules'));
            endwhile;
            wp_reset_postdata();
        }
    }

    ksort($found_modules);
    ksort($found_heros);

    foreach ($found_modules as &$pages) {
        ksort($pages);
    }
    foreach ($found_heros as &$pages) {
        ksort($pages);
    }

    $filtered_heros = filtered_heading($found_heros);
    $filtered_pages = filtered_heading($found_modules);
    ?>
    <div class="wrap">
        <h1>Search Modules</h1>
        <div class="search-modules-intro">
            <p>Overview of which ACF modules and heroes are used across all published pages. Each table shows a cross-reference between pages (rows) and modules (columns).</p>
            <p class="description">Use this to find where a specific module is being used, or to check which modules a page contains.</p>
        </div>
        <?php
            create_table('Heroes', $filtered_heros, $found_heros);
            create_table('Modules', $filtered_pages, $found_modules);
        ?>
    </div>
    <?php
}


function filtered_heading($found_content) {
    $filtered_content = [];
    foreach ($found_content as $module_data) {
        foreach ($module_data as $page => $value) {
            if ($value === '✔️' && !in_array($page, $filtered_content)) {
                $filtered_content[] = $page;
            }
        }
    }

    sort($filtered_content);

    return $filtered_content;
}

function create_table($table_title, $table_heading, $table_content) {
    if (empty($table_content)) return;
    ?>
    <div class="table-search-custom">
        <h3><?php echo esc_html($table_title); ?></h3>
        <table class="metrics-table">
            <thead>
                <tr>
                    <td></td>
                    <td colspan="<?php echo count($table_content); ?>">Modules</td>
                </tr>
                <tr>
                    <th>Page</th>
                    <?php foreach ($table_content as $content_name => $content) { ?>
                        <th><?php echo esc_html($content_name); ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($table_heading as $heading) {
                    $last_dash = strrpos($heading, '-');
                    $page_title = $last_dash !== false ? substr($heading, 0, $last_dash) : $heading;
                    $post_id = $last_dash !== false ? substr($heading, $last_dash + 1) : 0;
                ?>
                    <tr>
                        <td class="title-row"><a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>"><?php echo esc_html($page_title); ?></a></td>
                        <?php foreach ($table_content as $module_name => $module_content) { ?>
                            <td><?php echo isset($module_content[$heading]) ? esc_html($module_content[$heading]) : ''; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
