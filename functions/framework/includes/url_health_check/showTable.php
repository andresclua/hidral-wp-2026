<?php

function get_url_chequer_table_information(){
    global $wpdb;
    // Table Names for url_health_chequer
    $tableName = $wpdb->prefix . 'urlchecker';

    // Checks if the tables are created
    $tableExists = $wpdb->get_results("SHOW TABLES LIKE '$tableName'");

    if(!$tableExists){
        // Crates Tables if they are not created
        url_health_checker_content();
    }
    // Gets Table results for Lighthose and Standard Values
    $results = $wpdb->get_results("SELECT * FROM $tableName ORDER BY date DESC");
    // Returns an array 
    if (!empty($results)) {  
        return $results;
    }
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
    <h2 class="text--center">URLS with Errors</h2>
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
                 if(!$show_all){
                    $metricsData = array_slice($metricsData, -10);
                }
                foreach ($metricsData as $key => $singleData) {?>
                    <tr>
                        <td ><?php $newDate = new DateTime($singleData->date); echo $newDate->format('j M y H:i:s');  ?></td>
                        <td class="<?= setColor(esc_html($singleData->url_name) , esc_html($metricsData->url_name)) ?>"><?= esc_html($singleData->url_name)  ?></td>
                        <td class="<?= setColor(esc_html($singleData->error_status) , esc_html($metricsData->error_status)) ?>"><?= esc_html($singleData->error_status) ?></td>
                        <td class="<?= setColor(esc_html($singleData->error), esc_html($metricsData->error)) ?>"><?= esc_html($singleData->error)  ?></td>
                    </tr>
            <?php } ?>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>