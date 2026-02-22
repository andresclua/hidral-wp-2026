<?php

/**
 * Send an alert email when the site indexing status is changed.
 *
 * This function sends an alert email to notify the recipient that the 
 * website's indexing status has been corrected to ensure it is indexed
 * or not indexed as appropriate for the environment.
 *
 * @return void
 */

function detect_robot_callback()
{ 
    $home_url = home_url();
    $parsed_url = parse_url($home_url);
    $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
    $site_indexed = get_option('blog_public');

    // Check if the base URL contains the dev identifier string (indicating a dev environment)
    if (strpos($base_url, DEV_IDENTIFIER) === false) {
        if (!$site_indexed) {
            update_option('blog_public', 1); // Set the site to be indexed
            send_alert_email('site_not_indexed', $base_url); // Send an alert email
        }
    } else {
        if ($site_indexed) {
            update_option('blog_public', 0); // Set the site to not be indexed
            send_alert_email('site_indexed', $base_url); // Send an alert email
        }
    }
}

function send_alert_email($status, $url)
{
    $to = array('andres@terrahq.com', 'maria@terrahq.com');
    $subject = '';
    $message = '';

    if ($status == 'site_not_indexed') {
        $subject = 'ALERT: Website ' . $url . ' was not indexed';
        $message = 'The website at ' . $url . ' was not indexed. It has been fixed and now it is indexed as expected.';
    } elseif ($status == 'site_indexed') {
        $subject = 'ALERT: Website ' . $url . ' was indexed';
        $message = 'The website at ' . $url . ' was indexed. It has been fixed and now it is not indexed anymore.';
    }
    $mail = new Mail_To((object) array(
        'email' => $to,  // Email address to be used in the class
        'subject' => $subject,                // Interval (in seconds) for some functionality in the class
        'message' => $message,           // URL to be used in the class
    ));
    $mail->send_email_to();
}
