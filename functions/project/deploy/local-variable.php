<?php
    /**
     * Define constants for environment and URL detection
     *
     * The IS_VITE_DEVELOPMENT constant determines if the environment is in development mode or not. 
     * This flag is crucial for enabling Vite's hot-reload and development features. Set to true for VIRTUAL.
     *
     * The DEV_IDENTIFIER constant is used by a function (cronjob.php) that checks if the URL contains the keyword 'engine'. 
     * If the keyword is present, the function automatically checks the 'robots' checkbox in the WordPress backend, 
     * setting the page to 'noindex, nofollow' for search engines, thus preventing it from being indexed.
     */

    define('IS_VITE_DEVELOPMENT', true);
    define('DEV_IDENTIFIER', 'engine');

?>
