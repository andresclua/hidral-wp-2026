<?php

function get_url_chequer_table_information(){
    global $wpdb;
    $tableName = $wpdb->prefix . 'urlchecker';

    $tableExists = $wpdb->get_var("SHOW TABLES LIKE '$tableName'");

    if (!$tableExists) {
        return [];
    }

    $results = $wpdb->get_results("SELECT * FROM $tableName ORDER BY date DESC");
    return !empty($results) ? $results : [];
}

function show_urlhealthchequer_dashboard_table(){
    $show_all = false;
    wp_enqueue_style('url-chequer-style', get_template_directory_uri() . '/functions/framework/includes/url_health_check/style.css');
    $results = get_url_chequer_table_information();
    ?>
      <div class="tf-url_health_chequer">
        <?php  
            loadURLSHTML($results,  $show_all);
        ?>
    </div>
    <?php 
}

function show_url_health_chequer_table(){  
    $show_all = true;
    wp_enqueue_style('url-chequer-style', get_template_directory_uri() . '/functions/framework/includes/url_health_check/style.css');
    $results = get_url_chequer_table_information();
?>
    <div class="tf-url_health_chequer">
        <?php  
            loadURLSHTML($results, $show_all);
        ?>
    </div>
<?php }

function loadURLSHTML($metricsData, $show_all){ ?>
    <h2 class="text--center">URLs with Errors</h2>
    <?php
        $interval_map = array('86400' => 'every day', '604800' => 'every week', '2592000' => 'every month');
        $current_interval = get_option('terra_sw_interval', '604800');
        $interval_label = isset($interval_map[$current_interval]) ? $interval_map[$current_interval] : 'periodically';
    ?>
    <?php
        $health_emails = get_option('terra_sw_emails', []);
        $health_emails = array_filter($health_emails);
        $emails_label = !empty($health_emails) ? implode(', ', array_map('esc_html', $health_emails)) : 'no recipients configured';
    ?>
    <p class="description" style="margin: 0 0 12px;">
        URLs that returned errors (404s, 500s, timeouts, broken redirects) during automated health checks. Runs <strong><?php echo esc_html($interval_label); ?></strong>, reports sent to <strong><?php echo $emails_label; ?></strong> (configurable in the Settings tab). <?php if (!$show_all) : ?>Showing the last 10 results.<?php endif; ?>
    </p>
    <table class="metrics-table">
        <thead>
            <tr>
                <th>DATE</th>
                <th>URL</th>
                <th>STATUS</th>
                <th>ERROR</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($metricsData)) {
                if (!$show_all) {
                    $metricsData = array_slice($metricsData, -10);
                }
                foreach ($metricsData as $singleData) { ?>
                    <tr>
                        <td><?php $newDate = new DateTime($singleData->date); echo $newDate->format('j M y H:i:s'); ?></td>
                        <td><?php echo esc_html($singleData->url_name); ?></td>
                        <td><?php echo esc_html($singleData->error_status); ?></td>
                        <td><?php echo esc_html($singleData->error); ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr><td colspan="4" style="text-align:center;color:#666;">No errors found.</td></tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>