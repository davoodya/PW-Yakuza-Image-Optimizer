<?php
/**
 * Yakuza Image Optimizer — uninstall cleanup.
 *
 * Removes every plugin-owned artifact: settings, run states, locks,
 * transients, scheduled events, the debug log, and the original-image
 * backup directory. Optimized images themselves are intentionally left
 * in place — they are the live media library after conversion, and
 * deleting them would break the site. Use the Backup tab's restore
 * feature before uninstalling if you want the originals back.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Scheduled Events
|--------------------------------------------------------------------------
*/

wp_clear_scheduled_hook('yio_bulk_cron');
wp_clear_scheduled_hook('yio_restore_cron');

/*
|--------------------------------------------------------------------------
| Options & Transients
|--------------------------------------------------------------------------
*/

delete_option('yio_settings');
delete_option('yio_bulk_state');
delete_option('yio_bulk_lock');
delete_option('yio_restore_state');
delete_option('yio_restore_lock');

delete_transient('yio_bulk_queue');
delete_transient('yio_restore_queue');

/*
|--------------------------------------------------------------------------
| Debug Log
|--------------------------------------------------------------------------
*/

$upload = wp_upload_dir();

$log_file = $upload['basedir'] . '/yio-debug.log';

if (is_file($log_file)) {
    @unlink($log_file);
}

/*
|--------------------------------------------------------------------------
| Original-Image Backups
|--------------------------------------------------------------------------
*/

function yio_uninstall_rrmdir($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {

        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            yio_uninstall_rrmdir($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}

yio_uninstall_rrmdir($upload['basedir'] . '/original-img');
