<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Backup Engine
|--------------------------------------------------------------------------
*/


/**
 * Create original image backup
 *
 * @param string $file
 * @return string|false
 */

function yio_create_backup($file)
{


    if (!file_exists($file)) {

        return false;

    }



    /*
    |--------------------------------------------------------------------------
    | Destination
    |--------------------------------------------------------------------------
    */


    $backup = yio_prepare_backup_folder($file);



    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Backup
    |--------------------------------------------------------------------------
    */


    if (file_exists($backup)) {

        return $backup;

    }



    /*
    |--------------------------------------------------------------------------
    | Copy Original
    |--------------------------------------------------------------------------
    */


    if (
        copy(
            $file,
            $backup
        )
    ) {


        yio_log(
            'Backup created: ' . $backup
        );


        return $backup;


    }



    yio_log(
        'Backup failed: ' . $file
    );


    return false;


}