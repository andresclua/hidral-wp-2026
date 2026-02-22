<?php

/**
 * Function `lighthouse_report_page_content`
 * 
 * This function creates the necessary tables in the WordPress database to store Lighthouse reports.
 * It is executed every time the report page is loaded in the admin panel.
 * 
 * - Creates two tables:
 *   1. `lighthouse`: Stores performance, accessibility, best practices, and SEO scores for both mobile and desktop devices.
 *   2. `lighthouseMetrics`: Stores the standard values for each metric.
 * 
 * If the tables do not exist, they are created and default values are inserted.
 */

function lighthouse_report_page_content(){
    global $wpdb;

    // Nombre de la tabla (usa el prefijo de la base de datos de WordPress)
    $tableName = $wpdb->prefix . 'lighthouse';
    $metricsTable = $wpdb->prefix . 'lighthouseMetrics';

    // Charset y collation
    $charset_collate = $wpdb->get_charset_collate();

    // Query SQL para crear la tableName
    $sql = "CREATE TABLE IF NOT EXISTS $tableName (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        mobile_performance varchar(255) NOT NULL,
        mobile_accessibility varchar(255) NOT NULL,
        mobile_best_practice varchar(255) NOT NULL,
        mobile_seo varchar(255) NOT NULL,
        desktop_performance varchar(255) NOT NULL,
        desktop_accessibility varchar(255) NOT NULL,
        desktop_best_practice varchar(255) NOT NULL,
        desktop_seo varchar(255) NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Ejecutar la query
    dbDelta($sql);

    $tableExists = $wpdb->get_results("SHOW TABLES LIKE '$metricsTable'");
    if(!$tableExists){
        // Query SQL para crear la tableName
        $sql = "CREATE TABLE IF NOT EXISTS $metricsTable (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            desktop_performance int NOT NULL,
            desktop_accessibility int NOT NULL,
            desktop_best int NOT NULL,
            desktop_seo int NOT NULL,
            mobile_performance int NOT NULL,
            mobile_accessibility int NOT NULL,
            mobile_best int NOT NULL,
            mobile_seo int NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Ejecutar la query
        dbDelta($sql);

        $wpdb->insert(
            $metricsTable, //Table name
            array(
                'desktop_performance' => 80,
                'desktop_accessibility'  => 90,
                'desktop_best' => 90,
                'desktop_seo'  => 100,
                'mobile_performance' => 80,
                'mobile_accessibility'  => 90,
                'mobile_best' => 90,
                'mobile_seo'  => 100,
                'date'  => current_time('mysql'), // Actual timestamo in  MySQL format
            ),
            array(
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%d', // format value (integer)
                '%s'  // format value (datetime)
            )
        );
    }
  
    save_lighthouse_information();
}


function get_lighthouse_report($url, $strategy, $category) {
    $apiKey = 'AIzaSyCyXdxMw5nFl8mxmT9H8kTaJ4J8B66T978';  // Replace with your API key
    $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode($url) . '&strategy=' . $strategy . '&key=' . $apiKey;
    $apiUrl .= '&category=' . $category; // Append the category to the API URL

    // Make the request to the API with a longer timeout (e.g., 20 seconds)
    $response = wp_remote_get($apiUrl, array('timeout' => 1000)); // Timeout set to 20 seconds

    // Check if there was an error with the request
    if (is_wp_error($response)) {
        return 'API request error: ' . $response->get_error_message();
    }

    // Get the body of the response
    $body = wp_remote_retrieve_body($response);
    
    // Check if the body of the response is empty
    if (empty($body)) {
        return 'Error: The response body is empty.';
    }

    // Decode the JSON response
    $data = json_decode($body, true);
    
    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        return 'JSON decoding error: ' . json_last_error_msg();
    }

    // Return the decoded data for inspection
    return $data;
}

function save_lighthouse_information(){
    global $wpdb;
    global $terraLighthouse;
    $url = $terraLighthouse->url;

    $mobileReportP = get_lighthouse_report($url, 'MOBILE','PERFORMANCE');
    $desktopReportP = get_lighthouse_report($url, 'DESKTOP', 'PERFORMANCE');

    $mobileReportA = get_lighthouse_report($url, 'MOBILE','ACCESSIBILITY');
    $desktopReportA = get_lighthouse_report($url, 'DESKTOP', 'ACCESSIBILITY');

    $mobileReportB = get_lighthouse_report($url, 'MOBILE','BEST_PRACTICES');
    $desktopReportB = get_lighthouse_report($url, 'DESKTOP', 'BEST_PRACTICES');

    $mobileReportS = get_lighthouse_report($url, 'MOBILE','SEO');
    $desktopReportS = get_lighthouse_report($url, 'DESKTOP', 'SEO');

    $tableName = $wpdb->prefix . 'lighthouse';

    $wpdb->insert(
        $tableName, //Table name
        array(
            'mobile_performance' => $mobileReportP['lighthouseResult']['categories']['performance']['score'] * 100,
            'desktop_performance'  => $desktopReportP['lighthouseResult']['categories']['performance']['score'] * 100,

            'mobile_accessibility' => $mobileReportA['lighthouseResult']['categories']['accessibility']['score'] * 100,
            'desktop_accessibility'  => $desktopReportA['lighthouseResult']['categories']['accessibility']['score'] * 100,

            'mobile_best_practice' => $mobileReportB['lighthouseResult']['categories']['best-practices']['score'] * 100,
            'desktop_best_practice'  => $desktopReportB['lighthouseResult']['categories']['best-practices']['score'] * 100,

            'mobile_seo' => $mobileReportS['lighthouseResult']['categories']['seo']['score'] * 100,
            'desktop_seo'  => $desktopReportS['lighthouseResult']['categories']['seo']['score'] * 100,
            
            'date'  => current_time('mysql'), // Actual timestamo in  MySQL format
        ),
        array(
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%d', // format value (integer)
            '%s'  // format value (datetime)
        )
    );

    // Comprobar si la inserción fue exitosa
    if ($wpdb->insert_id) {
        check_metric_and_lighthouse_values();
        delete_from_db_after_month();
    }
}

function check_metric_and_lighthouse_values(){
    global $wpdb;
    // Name of the lighthouse table using the WordPress database prefix
    $tableName = $wpdb->prefix . 'lighthouse';

    // Check if the lighthouse table exists
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$tableName'");
    
    // If the lighthouse table exists, proceed
    if($exists){
        // Name of the lighthouse metrics table
        $metricsTable = $wpdb->prefix . 'lighthouseMetrics';

        // Fetch all rows from the lighthouseMetrics table
        $metricResults = $wpdb->get_results("SELECT * FROM $metricsTable ORDER BY date DESC");

        // If metrics table has results, proceed
        if($metricResults){
            // Fetch all rows from the lighthouse table
            $lighthouseResults = $wpdb->get_results("SELECT * FROM $tableName ORDER BY date DESC");

            // Extract standard values from the first row of the metrics table
            $performanceDesktop = $metricResults[0]->desktop_performance;
            $accessibilityDesktop = $metricResults[0]->desktop_accessibility;
            $bestDesktop = $metricResults[0]->desktop_best;
            $seoDesktop = $metricResults[0]->desktop_seo;

            $derformanceMobile = $metricResults[0]->mobile_performance;
            $accessibilityMobile = $metricResults[0]->mobile_accessibility;
            $bestMobile = $metricResults[0]->mobile_best;
            $seoMobile = $metricResults[0]->mobile_seo;
            
            if($lighthouseResults){
                // Initialize the message that will be sent if any values are lower than expected
                $message = "The Lighthouse report highlights an issue worth reviewing. <br><br>";
                // Check if desktop or mobile performance is below the standard performance value
                $errorPD = create_error_message($lighthouseResults[0]->desktop_performance,  "Performance", $performanceDesktop, "Desktop");
                $errorAD = create_error_message($lighthouseResults[0]->desktop_accessibility, "Accessibility", $accessibilityDesktop, "Desktop");
                $errorBD = create_error_message($lighthouseResults[0]->desktop_best_practice, "Best Practice", $bestDesktop , "Desktop");
                $errorSD = create_error_message($lighthouseResults[0]->desktop_seo, "SEO", $seoDesktop, "Desktop");

                $errorPM = create_error_message($lighthouseResults[0]->mobile_performance, "Performance", $derformanceMobile, "Mobile");
                $errorAM = create_error_message($lighthouseResults[0]->mobile_accessibility, "Accessibility", $accessibilityMobile, "Mobile");
                $errorBM = create_error_message( $lighthouseResults[0]->mobile_best_practice, "Best Practice", $bestMobile, "Mobile");
                $errorSM = create_error_message($lighthouseResults[0]->mobile_seo, "SEO", $seoMobile, "Mobile");

                if($errorPD || $errorAD || $errorBD || $errorSD || $errorPM || $errorAM || $errorBM || $errorSM ){
                    $message .=  $errorPD;
                    $message .=  $errorAD;
                    $message .=  $errorBD;
                    $message .=  $errorSD;
                    $message .=  $errorPM;
                    $message .=  $errorAM;
                    $message .=  $errorBM;
                    $message .=  $errorSM;
                    $message .="<ul><li><strong>Desktop</strong></li><ul><br>";
                    $message .="<li>".$lighthouseResults[0]->desktop_performance." Performance</li><br>";
                    $message .="<li>".$lighthouseResults[0]->desktop_accessibility." Accessibility</li><br>";
                    $message .="<li>".$lighthouseResults[0]->desktop_best_practice." Best Practices</li><br>";
                    $message .="<li>".$lighthouseResults[0]->desktop_seo." SEO</li></ul></ul><br>";

                    $message .="<ul><li><strong>Mobile</strong></li><ul><br>";
                    $message .="<li>".$lighthouseResults[0]->mobile_performance." Performance</li><br>";
                    $message .="<li>".$lighthouseResults[0]->mobile_accessibility." Accessibility</li><br>";
                    $message .="<li>".$lighthouseResults[0]->mobile_best_practice." Best Practices</li><br>";
                    $message .="<li>".$lighthouseResults[0]->mobile_seo." SEO</li></ul></ul><br>";

                    $message .= "The minimum values you've set trigger this report with the following thresholds:<br><br>";
                    $message .="<ul><li><strong>Desktop</strong></li><ul><br>";
                    $message .="<li>Performance: ".$performanceDesktop." </li><br>";
                    $message .="<li>Accessibility: ".$accessibilityDesktop." </li><br>";
                    $message .="<li>Best Practices: ".$bestDesktop."</li><br>";
                    $message .="<li>SEO: ".$seoDesktop."</li></ul></ul><br><br>";

                    $message .="<ul><li><strong>Mobile</strong></li><ul><br>";
                    $message .="<li>Performance: ".$derformanceMobile." </li><br>";
                    $message .="<li>Accessibility: ".$accessibilityMobile." </li><br>";
                    $message .="<li>Best Practices: ".$bestMobile."</li><br>";
                    $message .="<li>SEO: ".$seoMobile."</li></ul></ul><br><br>";
                    
                    $message .="For more details, click <a href='".get_site_url()."/wp-admin/admin.php?page=lighthouse-report'>here</a>.";

                    send_notification_email($message);
                }
            }
        }
    }
}
/**
 * Function `delete_from_db_after_month`
 * 
 * This function deletes the oldest row from the Lighthouse table if it is older than one month.
 * It checks the `date` field of the earliest entry and compares it with the current date. 
 * If the entry is from a previous month or year, it will be removed from the database.
 */
function delete_from_db_after_month(){
    global $wpdb;
    // Name of the Lighthouse table, using WordPress table prefix
    $tableName = $wpdb->prefix . 'lighthouse';

    // Fetch the oldest row (based on the date) from the Lighthouse table
    $results = $wpdb->get_results("SELECT id, date FROM $tableName ORDER BY date ASC LIMIT 1");

    // Check if there is a result (i.e., if there's data in the table)
    if($results){
        // Create a DateTime object from the date of the oldest row
        $lastMonth = new DateTime($results[0]->date); 
        if($lastMonth){
            // Get the month and year of the oldest row
            $lastYear = $lastMonth->format('Y');   // Extract year
            $lastMonth = $lastMonth->format('m');  // Extract month

            // Get the current month and year in MySQL format
            $newMonth = new DateTime(current_time('mysql')); 
            $newYear = $newMonth->format('Y');     // Extract current year
            $newMonth = $newMonth->format('m');    // Extract current month

            // Check if the oldest record is from a previous month or year
            if($lastMonth < $newMonth || $lastYear < $newYear){
                // If it's older, delete the record from the table based on its ID
                $idRow = $results[0]->id;  // Get the ID of the oldest row
                $wpdb->delete(
                    $tableName,            // Table name
                    array('id' => $idRow),  // Condition: where ID matches
                    array('%d')             // Data format (integer)
                );
            }
        }
    }
}
/**
 * Function `send_notification_email`
 * 
 * This function sends an email notification when Lighthouse report issues are detected. 
 * It pulls the email and URL information from the global `terraLighthouse` object and 
 * creates a new instance of the `Mail_To` class to send the email. The subject of the 
 * email includes the URL of the report, and the message contains detailed information 
 * about the issues.
 * 
 * @param string $message The body of the email, typically containing the report issues and details.
 */
function send_notification_email($message){
    // Ensure 'wpengine' is not part of the site URL before sending email
    if (strpos(get_site_url(), 'wpengine') === false) { 
        global $terraLighthouse;
        $email = $terraLighthouse->email;
        $url = $terraLighthouse->url;

        // Create an instance of the Mail_To class with specified parameters
        $sendMail = new Mail_To((object) array(
            'email' => $email,  // Email address to be used in the class
            'subject' => get_bloginfo() . " Lighthouse Report Issues",  // Email subject
            'message' => $message,  // Email body containing Lighthouse issues
        ));
        
        // Send the email
        $sendMail->send_email_to();
    }
}


/**
 * Function `create_error_message`
 * 
 * This function generates an error message when either desktop or mobile Lighthouse scores 
 * (performance, accessibility, best practices, SEO) fall below a specified metric value. 
 * The function compares the current scores against the standard values and builds 
 * a message for the failing metrics.
 * 
 * @param int $lighthouseValue The Lighthouse score for desktop.
 * @param int $mobileValue The Lighthouse score for mobile.
 * @param string $type The type of score being evaluated (e.g., Performance, Accessibility).
 * @param int $metricValue The standard or threshold value that the scores are compared against.
 * 
 * @return string An error message listing which scores are below the metric value, 
 *                including the actual vs expected values.
 */
function create_error_message($lighthouseValue, $type, $metricValue, $devide) {
    // Initialize an empty string for the error message
    
    $errorIs = "";
    // Check if the desktop score is below the standard metric value
    if ($lighthouseValue < $metricValue && $lighthouseValue > 10) {
        $errorIs .= "The problem is with the Desktop ". $type  . " score, which returned ". $lighthouseValue .", while your minimum acceptable value is set to ".$metricValue.".<br>";
    }
    // Return the generated error message
    return $errorIs ? $errorIs : false;
}
