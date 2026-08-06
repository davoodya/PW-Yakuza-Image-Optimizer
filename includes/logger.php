<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| YIO Logger
|--------------------------------------------------------------------------
*/


function yio_log($message)
{

    if (!yio_get_option('logging', 1)) {

        return;

    }

    $upload_dir = wp_upload_dir();

    $log_file = $upload_dir['basedir'] . '/yio-debug.log';

    $time = current_time('Y-m-d H:i:s');

    $entry = sprintf(
        "[%s] %s\n",
        $time,
        $message
    );

    // Never let a log write emit a PHP warning (e.g. unwritable uploads
    // dir or disk full): on AJAX endpoints such a warning would corrupt
    // the JSON response and make uploads fail.
    if (!is_dir($upload_dir['basedir'])) {
        return;
    }

    @file_put_contents(
        $log_file,
        $entry,
        FILE_APPEND | LOCK_EX
    );

}