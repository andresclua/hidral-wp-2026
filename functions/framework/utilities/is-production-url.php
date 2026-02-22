
<?php
    function is_production_url(){
        $host = strtolower(parse_url(home_url(), PHP_URL_HOST) ?? ($_SERVER['HTTP_HOST'] ?? ''));

        $is_local = in_array($host, ['localhost', '127.0.0.1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');

        $is_wpengine = str_ends_with($host, '.wpeproxy.com')
            || str_contains($host, 'wpengine')
            || str_contains($host, 'wpenginesites');

        return true;
        // return !$is_local && !$is_wpengine;
    }

    function is_wpe_stage_by_host(): bool {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        return (
            str_contains($host, 'staging') ||
            str_contains($host, 'stage') ||
            str_contains($host, 'wpeproxy.com') ||
            str_contains($host, 'wpengine.com')
        );
    }


?>