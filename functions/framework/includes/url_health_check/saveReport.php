<?php

/**
 * Function `url_health_checker_content`
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

function url_health_checker_content(){
    global $wpdb;

    // Nombre de la tabla (usa el prefijo de la base de datos de WordPress)
    $tableName = $wpdb->prefix . 'urlchecker';

    // Charset y collation
    $charset_collate = $wpdb->get_charset_collate();

    $tableExists = $wpdb->get_results("SHOW TABLES LIKE '$tableName'");
    if(!$tableExists){
        // Query SQL para crear la tableName
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url_name varchar(128) NOT NULL,
            error_status int NOT NULL,
            error varchar(256) NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        // Ejecutar la query
        dbDelta($sql);
        
    }
  
    save_url_health_chequer_information();
}


function save_url_health_chequer_information(){
    global $wpdb;
    global $terraLighthouse;
    $url = $terraLighthouse->url;

    $problems = run_check();

    $tableName = $wpdb->prefix . 'urlchecker';

    if($problems){
        foreach ($problems as $key => $singleProblem) {
            $wpdb->insert(
                $tableName, //Table name
                array(
                    'url_name' =>  $singleProblem['url'],
                    'error_status'  => $singleProblem['code'],
                    'error'  => $singleProblem['error'],
                    'date'  => current_time('mysql'), // Actual timestamo in  MySQL format
                ),
                array(
                    '%s', // format value (string)
                    '%d', // format value (integer)
                    '%s', // format value (string)
                    '%s'  // format value (datetime)
                )
            );

            // Comprobar si la inserción fue exitosa
            if ($wpdb->insert_id) {
                // check_metric_and_lighthouse_values();
            }
        }
    }
    
}

    function run_check() {
        // $urls = [
        //     // 'https://terrahq.com/',
        //     // 'https://stgstifelinst.wpengine.com/event/biotech-executive-ski-summit/',
        //     'https://bbhmdev.wpenginepowered.com/?sdfdfsdfdf3'
        // ];
        $urls = get_posts([
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        if (!is_array($urls) || empty($urls)) return;
        $results = [];
        foreach ($urls as $url) {
            $url = get_the_permalink($url);
            if (!$url) continue;

            $results[] = check_url($url);
        }

        $problems = array_filter($results, function($row) {
            return $row['ok'] === false;
        });
        return $problems;
        if (!empty($problems)) {
            notify($problems);
        }
    }

    /**
     * Checks one URL and returns status.
     * Adjust "allowed_codes" if you want to accept redirects as OK.
     */
    function check_url($url) {
        $allowed_codes = [200, 301, 302]; // <-- si querés SOLO 200, dejalo en [200]

        $args = [
        'timeout'     => 12,
        'redirection' => 5,
        'headers'     => [
            'User-Agent' => 'TerraURLHealthCheck/1.0 (+WordPress)',
        ],
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return [
                'url' => $url,
                'ok' => false,
                'code' => 0,
                'error' => $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);

        return [
            'url' => $url,
            'ok' => in_array($code, $allowed_codes, true),
            'code' => $code,
            'error' => '',
        ];
    }

     /**
     * Email notification (simple)
     */
    function notify($problems) {
        $to = 'nerea@terrahq.com';
        $subject = '[WP] URL Health Check: issues detected';

        $lines = [];
        foreach ($problems as $p) {
        $lines[] = sprintf('- %s => %s%s',
            $p['url'],
            $p['code'] ? $p['code'] : 'ERROR',
            $p['error'] ? (' (' . $p['error'] . ')') : ''
        );
        }

        $message = "These URLs have issues:\n\n" . implode("\n", $lines) . "\n\n"
            . "Timestamp (WP): " . current_time('mysql') . "\n";

        new Mail_To((object) array(
            'email' => $to,  // Email address to be used in the class
            'subject' => $subject,                // Interval (in seconds) for some functionality in the class
            'message' => $message          // URL to be used in the class
        ));
    }
