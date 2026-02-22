<?php
/**
 * Class Call_Cronjob
 *
 * Function to add and execute a custom cron job.
 *
 * @param string $cronName     The name of the cron job.
 * @param int    $interval     The interval in seconds at which the cron job will run.
 * @param string $functionName The name of the function that will be executed by the cron job.
 *
 * @example
 * new Call_Cronjob((object) array(
 *     'cronName' => 'every_one_hour',       // Name of the cron job
 *     'interval' => 3600,                      // Interval (in seconds) to run the cron job (12 hours)
 *     'functionName' => 'lighthouse_report_page_content' // Function to be executed by the cron job
 * ));
 */
class Call_Cronjob {
    private $cronName;      // The name of the cron job
    private $interval;      // The interval in seconds
    private $functionName;  // The function to execute

    /**
     * Constructor for Call_Cronjob.
     *
     * @param object $config The configuration object with cronName, interval, and functionName.
     */
    public function __construct($config) {
        $this->cronName = $config->cronName;
        $this->interval = $config->interval;
        $this->functionName = $config->functionName;

        add_action('init', array($this, 'call_cronjob'));
    }

    public function call_cronjob() {
        // Add a filter to create a custom cron schedule with the specified interval.
        $cronName = $this->cronName;  
        $interval = $this->interval; 
        add_filter('cron_schedules', function($schedules) use ($cronName, $interval) {
            $schedules[$cronName] = array(
                'interval' => $interval,  // Interval in seconds
                'display'  => 'Every ' . ($interval / 60) . ' minutes'  // Display the interval in minutes
            );
            return $schedules;
        });

        // Check if the cron event is already scheduled.
        if (!wp_next_scheduled('execute_' . $cronName)) {
            // Schedule the cron event to run at the specified interval.
            wp_schedule_event(time(), $cronName, 'execute_' . $cronName);
        }

        // Hook the cron action to the specified function.
        add_action('execute_' . $cronName,  $this->functionName);
    }
}
?>
