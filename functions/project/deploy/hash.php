<?php

/**
 * Define a hash constant for asset versioning
 *
 * This hash is used only in production mode. Vite is responsible for generating
 * the hash, creating a new one every time the project is built. The hash is 
 * applied to CSS and JS files for cache busting purposes.
 *
 * Usage: This constant is utilized in functions/project/enqueues.php
 */
define('hash', 'yp0');
define('SPLING_API_KEY', '2f30596290a6962ccaa91e9d015cfdd7cd217d36ecc5452fb51d6efd2254b7e4');

?>