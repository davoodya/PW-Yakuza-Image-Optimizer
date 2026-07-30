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
    return get_option('yio_settings', []);
}

/*
|--------------------------------------------------------------------------
| Get Option
|--------------------------------------------------------------------------
*/

function yio_get_option($key, $default = null)
{
    $options = yio_get_options();

    return $options[$key] ?? $default;
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
| Original Images Directory
|--------------------------------------------------------------------------
*/

function yio_original_dir()
{
    $upload = wp_upload_dir();

    return trailingslashit($upload['basedir']) . 'original-img/';
}

/*
|--------------------------------------------------------------------------
| Create Original Folder
|--------------------------------------------------------------------------
*/

function yio_create_original_dir()
{
    $dir = yio_original_dir();

    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }

    return $dir;
}

/*
|--------------------------------------------------------------------------
| Current Output Format
|--------------------------------------------------------------------------
*/

function yio_output_extension()
{
    $format = strtolower(
        yio_get_option('output_format', 'webp')
    );

    if ($format == 'avif' && yio_supports_avif()) {
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
    if (!extension_loaded('imagick')) {
        return false;
    }

    try {

        $formats = Imagick::queryFormats('AVIF');

        return !empty($formats);

    } catch (Exception $e) {

        return false;

    }
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
| Is Image File
|--------------------------------------------------------------------------
*/

function yio_is_supported_image($path)
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return in_array($ext, yio_supported_extensions());
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

    return size_format(filesize($file), 2);
}

/*
|--------------------------------------------------------------------------
| Build Backup Path
|--------------------------------------------------------------------------
*/

function yio_backup_path($absolute_file)
{
    $upload = wp_upload_dir();

    $relative = str_replace(
        trailingslashit($upload['basedir']),
        '',
        $absolute_file
    );

    return yio_original_dir() . $relative;
}

/*
|--------------------------------------------------------------------------
| Ensure Backup Folder Exists
|--------------------------------------------------------------------------
*/

function yio_prepare_backup_folder($absolute_file)
{
    $backup = yio_backup_path($absolute_file);

    $folder = dirname($backup);

    if (!file_exists($folder)) {
        wp_mkdir_p($folder);
    }

    return $backup;
}

/*
|--------------------------------------------------------------------------
| Simple Logger
|--------------------------------------------------------------------------
*/

function yio_log($message)
{
    if (!defined('WP_DEBUG')) {
        return;
    }

    if (!WP_DEBUG) {
        return;
    }

    error_log('[YIO] ' . $message);
}