<?php

function get_table_information(){
    global $wpdb;
    // Table Names for Lighthouse
    $tableName = $wpdb->prefix . 'lighthouse';
    $metricsTableName = $wpdb->prefix . 'lighthouseMetrics';

    // Checks if the tables are created
    $tableExists = $wpdb->get_results("SHOW TABLES LIKE '$tableName'");
    $metricsTableExists = $wpdb->get_results("SHOW TABLES LIKE '$metricsTableName'");

    if(!$tableExists || !$metricsTableExists){
        // Crates Tables if they are not created
        lighthouse_report_page_content();
    }
    // Gets Table results for Lighthose and Standard Values
    $results = $wpdb->get_results("SELECT * FROM $tableName ORDER BY date DESC");
    $metricResults = $wpdb->get_results("SELECT * FROM $metricsTableName ORDER BY date DESC");
    
    // Returns an array 
    if (!empty($results) && !empty($metricResults)) {  
        return ["lighthouse" => $results, "metrics" => $metricResults];
    }
}
function show_lighthouse_dashboard_table(){
    $show_all = false;
    $results = get_table_information();
    wp_enqueue_style('lighthouse-style', get_template_directory_uri() . '/functions/framework/includes/lighthouse/style.css');
    ?>
     <div class="tf-lighthouse">
        <?php  
            loadTableHTML($results['lighthouse'], $results['metrics'][0],  $show_all);
        ?>
    </div>
    <?php 
}

function show_table(){  
    $results = get_table_information();
    $show_all = true;
    wp_enqueue_style('lighthouse-style', get_template_directory_uri() . '/functions/framework/includes/lighthouse/style.css');
    wp_enqueue_script('lighthouse-onChange', get_template_directory_uri() . '/functions/framework/includes/lighthouse/onChange.js', array(), false, true);  
    wp_localize_script('lighthouse-onChange', 'base_wp_api', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

?>
    <div class="tf-lighthouse">
        <?php  
            loadListHTML($results['metrics'][0]);
            loadTableHTML($results['lighthouse'], $results['metrics'][0], $show_all);
        ?>
    </div>
<?php }

function loadListHTML($metricsData){ ?>
    <h2 class="text--center">Minimum accepted scores</h2>
    <table class="metrics-table">
        <thead>
            <tr>
                <th colspan="2">Performance</th>
                <th colspan="2">Accessibility</th>
                <th colspan="2">Best Practice</th>
                <th colspan="2">SEO</th>
            </tr>
            <tr>
                <th>Mobile</th>
                <th>Desktop</th>
                <th>Mobile</th>
                <th>Desktop</th>
                <th>Mobile</th>
                <th>Desktop</th>
                <th>Mobile</th>
                <th>Desktop</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($metricsData)) {
                ?>
                    <tr>
                        <td ><?= '<input name="mobile_performance" type="number" min="0" max="100" value="' . esc_attr($metricsData->mobile_performance) .'">';  ?></td>
                        <td ><?= '<input name="desktop_performance" type="number" min="0" max="100" value="' . esc_attr($metricsData->desktop_performance) .'">';  ?></td>

                        <td ><?= '<input name="mobile_accessibility" type="number" min="0" max="100" value="' . esc_attr($metricsData->mobile_accessibility) .'">';  ?></td>
                        <td ><?= '<input name="desktop_accessibility" type="number" min="0" max="100" value="' . esc_attr($metricsData->desktop_accessibility) .'">';  ?></td>

                        <td ><?= '<input name="mobile_best" type="number" min="0" max="100" value="' . esc_attr($metricsData->mobile_best) .'">';  ?></td>
                        <td ><?= '<input name="desktop_best" type="number" min="0" max="100" value="' . esc_attr($metricsData->desktop_best) .'">';  ?></td>

                        <td ><?= '<input name="mobile_seo" type="number" min="0" max="100" value="' . esc_attr($metricsData->mobile_seo) .'">';  ?></td>
                        <td ><?= '<input name="desktop_seo" type="number" min="0" max="100" value="' . esc_attr($metricsData->desktop_seo) .'">';  ?></td>
                    </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } 

function loadTableHTML($lightshouseData, $metricsData, $show_all){ ?>
    <h2 class="text--center">PageSpeed Scores</h2>
    <div>
        <ul>
            <li><div class="tf--square tf--square-danger"></div>From 0 to 49 : Deficient</li>
            <li> <div class=" tf--square tf--square-warning"></div>From 50 to 89: Needs Improvement</li>
            <li> <div class=" tf--square tf--square-success"></div>From 90 to 100 : Good</li>
            <li><div class="tf--square tf--square-different"></div>Represents scores that fall below the minimum acceptable</li>
        </ul>
        <?php $interval= get_field('terra_system_warning_interval', 'option');
            $interval_text = $interval === '86400' ? 'every 24 hours' : ($interval === '604800' ? 'every week' : 'every month');
        ?>
        <p> A cron job runs <?= $interval_text ?> fetching data from the PageSpeed <a href='https://www.googleapis.com/pagespeedonline/v5/runPagespeed' target='_blank'>API</a> and storing it in the database. If any value requires improvement or falls below standards, an email will be sent to Terra. If the value is below 10, no notification will be sent.</p>
    </div>
    <table class="lighthouse-table">
        <thead>
            <tr>
                <th></th>
                <th colspan="2">Performance</th>
                <th colspan="2">Accessibility</th>
                <th colspan="2">Best Practice</th>
                <th colspan="2">SEO</th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Mobile</th>
                <th>Desktop</th>
                <th>Mobile</th>
                <th>Desktop</th>
                <th>Mobile</th>
                <th>Desktop</th>
                <th>Mobile</th>
                <th>Desktop</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($lightshouseData)) {
                if(!$show_all){
                    $lightshouseData = array_slice($lightshouseData, -10);
                }
                foreach ($lightshouseData as $row) { ?>
                    <tr>
                        <td ><?php $newDate = new DateTime($row->date); echo $newDate->format('j M y H:i:s');  ?></td>
                        <td class="<?= setColor(esc_html($row->mobile_performance) , esc_html($metricsData->mobile_performance)) ?>"><?= esc_html($row->mobile_performance)  ?></td>
                        <td class="<?= setColor(esc_html($row->desktop_performance) , esc_html($metricsData->desktop_performance)) ?>"><?= esc_html($row->desktop_performance) ?></td>

                        <td class="<?= setColor(esc_html($row->mobile_accessibility), esc_html($metricsData->mobile_accessibility)) ?>"><?= esc_html($row->mobile_accessibility)  ?></td>
                        <td class="<?= setColor(esc_html($row->desktop_accessibility), esc_html($metricsData->desktop_accessibility)) ?>"><?= esc_html($row->desktop_accessibility) ?></td>

                        <td  class="<?= setColor(esc_html($row->mobile_best_practice), esc_html($metricsData->mobile_best)) ?>"><?= esc_html($row->mobile_best_practice)  ?></td>
                        <td  class="<?= setColor(esc_html($row->desktop_best_practice), esc_html($metricsData->desktop_best)) ?>"><?= esc_html($row->desktop_best_practice) ?></td>

                        <td class="<?= setColor(esc_html($row->mobile_seo) , esc_html($metricsData->mobile_seo)) ?>" > <?= esc_html($row->mobile_seo)  ?></td>
                        <td class="<?= setColor(esc_html($row->desktop_seo) , esc_html($metricsData->desktop_seo)) ?>" > <?= esc_html($row->desktop_seo) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>
<?php 
// Function to determine CSS class based on input value
function setColor($lightHouseValue, $metricsValue) {
    if ($lightHouseValue <= 100 && $lightHouseValue >= 90 ) {
        $className = "success"; // High value, set class to 'success'
        if ($lightHouseValue < $metricsValue) {
            $className = "different"; // If value is higher, override to 'info'
        }
    } else if ( $lightHouseValue > 49 && $lightHouseValue < 90 ) {
        $className = "warning"; // Medium value, set class to 'warning'
        if ($lightHouseValue < $metricsValue) {
            $className = "different"; // If value is higher, override to 'info'
        }
    } else if ($lightHouseValue < 50) {
        $className = "danger"; // Low value, set class to 'danger'
        if ($lightHouseValue > $metricsValue) {
            $className = "different"; // If value is higher, override to 'info'
        }
    }
    return $className; // Return the appropriate CSS class name
}?>