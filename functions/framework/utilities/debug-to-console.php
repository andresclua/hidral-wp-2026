<?php
function debug_to_console($data)
{
    if (is_array($data) || is_object($data)) {
        echo '<script>console.log(' . json_encode($data) . ');</script>';
    } else {
        echo '<script>console.log("' . addslashes($data) . '");</script>';
    }
}