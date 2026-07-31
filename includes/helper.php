<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Plugin Options
|--------------------------------------------------------------------------
*/

function yio_get_options()
{

    if (function_exists('yio_default_settings')) {

        return wp_parse_args(
            get_option('yio_settings', []),
            yio_default_settings()
        );

    }


    return get_option(
        'yio_settings',
        []
    );

}



/*
|--------------------------------------------------------------------------
| Single Option
|--------------------------------------------------------------------------
*/

function yio_get_option($key, $default = null)
{

    $options = yio_get_options();


    return isset($options[$key])
        ? $options[$key]
        : $default;

}



/*
|--------------------------------------------------------------------------
| Upload Directory
|--------------------------------------------------------------------------
*/

function yio_upload_dir()
{
    return wp_upload_dir();
}



/*
|--------------------------------------------------------------------------
| Upload Base Path
|--------------------------------------------------------------------------
*/

function yio_get_upload_path()
{

    $upload = wp_upload_dir();


    return trailingslashit(
        $upload['basedir']
    );

}



/*
|--------------------------------------------------------------------------
| Original Directory
|--------------------------------------------------------------------------
*/

function yio_original_dir()
{

    return yio_get_upload_path()
        .
        'original-img/';

}



/*
|--------------------------------------------------------------------------
| Create Directory
|--------------------------------------------------------------------------
*/

function yio_create_directory($path)
{

    if (!file_exists($path)) {

        wp_mkdir_p($path);

    }


    return $path;

}



/*
|--------------------------------------------------------------------------
| Create Original Folder
|--------------------------------------------------------------------------
*/

function yio_create_original_dir()
{

    return yio_create_directory(
        yio_original_dir()
    );

}



/*
|--------------------------------------------------------------------------
| Output Extension
|--------------------------------------------------------------------------
*/

function yio_output_extension()
{

    $format = strtolower(
        yio_get_option(
            'output_format',
            'webp'
        )
    );


    if (
        $format === 'avif'
        &&
        yio_supports_avif()
    ) {

        return 'avif';

    }


    return 'webp';

}



/*
|--------------------------------------------------------------------------
| AVIF Support
|--------------------------------------------------------------------------
*/

function yio_supports_avif()
{

    static $result = null;


    if ($result !== null) {

        return $result;

    }


    $result = false;


    if (!extension_loaded('imagick')) {

        return false;

    }


    try {


        $formats = Imagick::queryFormats('AVIF');


        $result = !empty($formats);


    } catch (Exception $e) {


        $result = false;


    }


    return $result;

}



/*
|--------------------------------------------------------------------------
| Supported Extensions
|--------------------------------------------------------------------------
*/

function yio_supported_extensions()
{

    return [

        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
        'bmp',
        'tif',
        'tiff'

    ];

}



/*
|--------------------------------------------------------------------------
| Check Image
|--------------------------------------------------------------------------
*/

function yio_is_supported_image($path)
{

    if (!file_exists($path)) {

        return false;

    }


    $ext = strtolower(
        pathinfo(
            $path,
            PATHINFO_EXTENSION
        )
    );


    return in_array(
        $ext,
        yio_supported_extensions(),
        true
    );

}



/*
|--------------------------------------------------------------------------
| Human File Size
|--------------------------------------------------------------------------
*/

function yio_filesize($file)
{

    if (!file_exists($file)) {

        return '-';

    }


    return size_format(
        filesize($file),
        2
    );

}



/*
|--------------------------------------------------------------------------
| Relative Upload Path
|--------------------------------------------------------------------------
*/

function yio_relative_upload_path($file)
{

    return ltrim(

        str_replace(
            yio_get_upload_path(),
            '',
            $file
        ),

        '/'

    );

}



/*
|--------------------------------------------------------------------------
| Backup Path
|--------------------------------------------------------------------------
*/

function yio_backup_path($file)
{

    return yio_original_dir()
        .
        yio_relative_upload_path($file);

}



/*
|--------------------------------------------------------------------------
| Prepare Backup Destination
|--------------------------------------------------------------------------
*/

function yio_prepare_backup_folder($file)
{

    $backup = yio_backup_path($file);


    yio_create_directory(
        dirname($backup)
    );


    return $backup;

}