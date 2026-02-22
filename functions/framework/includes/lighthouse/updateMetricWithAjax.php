<?php
// Handle the AJAX request to save changes
add_action('wp_ajax_get_lighthouse_report', 'handle_lighthouse_report_request');

function handle_lighthouse_report_request() {
    // Access the global WordPress database object
    global $wpdb;

    // Retrieve parameters from the POST request and sanitize them
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : 'performance';
    $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : 'performance';
    
    // Define the table name
    $table = $wpdb->prefix . 'lighthouseMetrics'; // Replace 'lighthouseMetrics' with the actual table name if necessary
    
    // Prepare the data to be updated
    $updated_data = array(
        $name => $value,
    );
    
    // Define the condition for the update
    $condition = array(
        'id' => 1 // Update where the id is 1, for example
    );
    
    // Format for the updated data and the condition
    $data_format = array('%d'); // Example format: string and integer
    $condition_format = array('%d'); // Format for the condition
    
    // Perform the update operation
    $wpdb->update($table, $updated_data, $condition, $data_format, $condition_format);

    // Optionally call a function to check the values (assumed to be defined elsewhere)
    check_values();
    
    // Send a JSON response with the updated name and value concatenated, and a $datos variable (which is not defined here)
    wp_send_json($name . $value . $datos);
}
?>