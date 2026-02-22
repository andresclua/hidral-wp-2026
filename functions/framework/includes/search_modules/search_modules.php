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
                // Obtiene el label del módulo desde ACF
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

    // Ordena los módulos alfabéticamente
    ksort($found_modules);
    ksort($found_heros);

    // También ordena internamente los nombres de las páginas en cada módulo
    foreach ($found_modules as &$pages) {
        ksort($pages);
    }
    foreach ($found_heros as &$pages) {
        ksort($pages);
    }

    $filtered_heros = filtered_heading($found_heros);
    $filtered_pages = filtered_heading($found_modules);
    ?>
        <h3 class="search-modules-title">Search for Module</h3>
        <p>This extension lets you search by ACF module in hero or module</p>
        
    <?php
        create_table('Hero Pages', $filtered_heros, $found_heros);
        create_table('Module Pages', $filtered_pages, $found_modules);
    ?>

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

    // Ordena alfabéticamente las páginas
    sort($filtered_content);

    return $filtered_content;
}

function create_table($table_title, $table_heading, $table_content) { ?>
    <div class="table-search-custom">
        <table class="metrics-table">
            <thead>
                <tr>
                    <td></td>
                    <td colspan="<?php echo count($table_content); ?>">
                        MODULES
                    </td>
                </tr>
                <tr>
                    <th><?php echo esc_html($table_title); ?></th>
                    <?php foreach ($table_content as $content_name => $content) { ?>
                        <th><?php echo esc_html($content_name); ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($table_heading as $heading) { ?>
                    <tr>
                        <td class="title-row"><a style="color: white; text-decoration: none" class="title-row-anchor" href="<?=  get_edit_post_link(explode("-" ,$heading)[1]) ?>" > <?php echo explode("-" ,$heading)[0]; ?></a></td>
                        <?php foreach ($table_content as $module_name => $module_content) { ?>
                            <td><?php echo isset($module_content[$heading]) ? esc_html($module_content[$heading]) : ''; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>