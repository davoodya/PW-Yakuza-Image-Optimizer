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


    file_put_contents(
        $log_file,
        $entry,
        FILE_APPEND
    );

}