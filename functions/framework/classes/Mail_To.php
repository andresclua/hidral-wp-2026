<?php

/**
 * Mail_To Class
 * 
 * Handles the sending of email messages using WordPress's wp_mail function.
 * The class is designed to send emails with HTML content and specific subject
 * and recipient details, using the parameters provided during instantiation.
 *
 * Example usage:
 * $sendMail = new Mail_To((object) array(
 *     'email' => "xxxx@terrhq.com",        // Email address to be used in the class
 *     'subject' => "This is the subject", // Subject of the email
 *     'message' => "This is the message", // Content of the email
 * ));
 */
class Mail_To {
    private $message;  // The email message content
    private $email;    // The recipient's email address
    private $subject;  // The email subject

    /**
     * Constructor for Mail_To.
     *
     * @param object $config The configuration object for the mail. It should include the message, email, and subject.
     */
    public function __construct($config) {
        $this->message = $config->message;
        $this->email = $config->email;
        $this->subject = $config->subject;

        $this->send_email_to();
    }

    /**
     * Sends an email using WordPress's wp_mail function.
     *
     * This method will be executed when the 'wp_loaded' action hook is triggered. 
     * It sends the email to the specified recipient using the wp_mail function.
     *
     * @return void
     */
    public function send_email_to() {
        $to = $this->email;
        $subject = $this->subject;  // The subject of the email
        $message = $this->message;  // The content of the email

        // Set the headers to ensure the email is sent as HTML and uses UTF-8 encoding
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        // Send the email using WordPress's wp_mail function
        $sent = wp_mail($to, $subject, $message, $headers);

        if (!$sent) {
            error_log('Failed to send email.');
        } else {
            error_log('Email sent successfully.');
        }
    }
}


