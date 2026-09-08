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
define('hash', 'y87');

?>