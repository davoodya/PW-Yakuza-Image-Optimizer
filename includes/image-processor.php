<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Image Processor Init
|--------------------------------------------------------------------------
*/

function yio_image_processor_init()
{

    add_filter(
        'wp_generate_attachment_metadata',
        'yio_process_uploaded_image',
        10,
        2
    );

}


/*
|--------------------------------------------------------------------------
| Process New Upload
|--------------------------------------------------------------------------
*/

function yio_process_uploaded_image($metadata, $attachment_id)
{

    /*
    |--------------------------------------------------------------------------
    | Check Plugin Status
    |--------------------------------------------------------------------------
    */


    if (!yio_get_option('enabled', 1)) {

        return $metadata;

    }



    if (!yio_get_option('process_new_uploads', 1)) {

        return $metadata;

    }



    /*
    |--------------------------------------------------------------------------
    | Get File
    |--------------------------------------------------------------------------
    */


    $file = get_attached_file($attachment_id);



    if (!$file || !file_exists($file)) {

        return $metadata;

    }



    /*
    |--------------------------------------------------------------------------
    | Dry Run
    |--------------------------------------------------------------------------
    */


    if (yio_get_option('dry_run', 0)) {


        yio_log(
            'DRY RUN: ' . $file
        );


        return $metadata;

    }



    /*
    |--------------------------------------------------------------------------
    | Backup Placeholder
    |--------------------------------------------------------------------------
    */


    if (yio_get_option('backup_original', 1)) {


        yio_log(
            'Backup required: ' . $file
        );


        /*
            Real backup will be implemented
            in backup.php
        */

    }



    /*
    |--------------------------------------------------------------------------
    | Optimization Placeholder
    |--------------------------------------------------------------------------
    */


    yio_log(
        'Processing image: ' . $file
    );



    return $metadata;

}