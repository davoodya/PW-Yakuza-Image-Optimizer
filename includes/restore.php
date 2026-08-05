<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Restore Init
|--------------------------------------------------------------------------
*/

function yio_restore_init()
{
    add_action('wp_ajax_yio_restore_start', 'yio_restore_ajax_start');
    add_action('wp_ajax_yio_restore_step', 'yio_restore_ajax_step');
    add_action('wp_ajax_yio_restore_status', 'yio_restore_ajax_status');
    add_action('wp_ajax_yio_restore_cancel', 'yio_restore_ajax_cancel');

    // Continuation for background processing (WP-Cron).
    add_action('yio_restore_cron', 'yio_restore_cron_run');
}

/*
|--------------------------------------------------------------------------
| AJAX: Start / Resume
|--------------------------------------------------------------------------
*/

function yio_restore_ajax_start()
{
    check_ajax_referer('yio_restore', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
    }

    // The run UI sits inside the settings form, so persist the current
    // restore method (sanitized) together with the run request.
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {

        $sanitized = yio_sanitize_settings(wp_unslash($_POST['settings']));

        update_option(
            'yio_settings',
            array_merge(yio_get_options(), array_intersect_key($sanitized, array_flip(array('restore_method'))))
        );
    }

    // Resume support: reuse the queue of a paused run.
    $existing = yio_restore_state();

    if (
        !empty($existing['status'])
        && $existing['status'] === 'paused'
        && get_transient('yio_restore_queue')
    ) {

        $existing['status'] = 'running';

        yio_restore_state($existing);

        yio_restore_schedule_continuation();

        wp_send_json_success(yio_restore_progress());
    }

    yio_restore_reset_state();

    $ids = yio_restore_scan();

    if (empty($ids)) {

        wp_send_json_success(array(
            'status'    => 'done',
            'total'     => 0,
            'processed' => 0,
            'restored'  => 0,
            'skipped'   => 0,
            'failed'    => 0,
            'log'       => array('No images with backups found to restore.'),
        ));
    }

    set_transient('yio_restore_queue', $ids, 6 * HOUR_IN_SECONDS);

    yio_restore_state(array(
        'status'    => 'running',
        'total'     => count($ids),
        'processed' => 0,
        'restored'  => 0,
        'skipped'   => 0,
        'failed'    => 0,
        'started'   => time(),
        'log'       => array(),
    ));

    yio_restore_schedule_continuation();

    wp_send_json_success(yio_restore_progress());
}

/*
|--------------------------------------------------------------------------
| AJAX: Step / Status / Cancel
|--------------------------------------------------------------------------
*/

function yio_restore_ajax_step()
{
    check_ajax_referer('yio_restore', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
    }

    wp_send_json_success(yio_restore_process_batch());
}

function yio_restore_ajax_status()
{
    check_ajax_referer('yio_restore', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
    }

    wp_send_json_success(yio_restore_progress());
}

function yio_restore_ajax_cancel()
{
    check_ajax_referer('yio_restore', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
    }

    $state = yio_restore_state();

    if (!empty($state) && $state['status'] === 'running') {

        $state['status'] = 'paused';

        yio_restore_state($state);
    }

    wp_send_json_success(yio_restore_progress());
}

/*
|--------------------------------------------------------------------------
| Cron Continuation
|--------------------------------------------------------------------------
*/

function yio_restore_cron_run()
{
    if (!yio_get_option('background_processing', 1)) {
        return;
    }

    $state = yio_restore_state();

    if (empty($state) || $state['status'] !== 'running') {
        return;
    }

    yio_restore_process_batch();

    // Keep the run alive until it finishes.
    $state = yio_restore_state();

    if (!empty($state['status']) && $state['status'] === 'running') {
        wp_schedule_single_event(time() + 60, 'yio_restore_cron');
    }
}

function yio_restore_schedule_continuation()
{
    if (yio_get_option('background_processing', 1)) {
        wp_schedule_single_event(time() + 60, 'yio_restore_cron');
    }
}

/*
|--------------------------------------------------------------------------
| Batch Processing
|--------------------------------------------------------------------------
*/

/**
 * Process one batch of restores and return the progress snapshot.
 *
 * @return array
 */
function yio_restore_process_batch()
{
    wp_raise_memory_limit('image');

    if (function_exists('set_time_limit')) {
        @set_time_limit(300);
    }

    $state = yio_restore_state();

    if (empty($state) || $state['status'] !== 'running') {
        return yio_restore_progress();
    }

    if (!yio_restore_lock_acquire()) {
        return array_merge(yio_restore_progress(), array('busy' => true));
    }

    $queue = get_transient('yio_restore_queue');

    if (empty($queue)) {

        yio_restore_finish($state);

        yio_restore_lock_release();

        return yio_restore_progress();
    }

    $batch_size = min(100, max(1, (int) yio_get_option('bulk_batch_size', 20)));

    $batch = array_splice($queue, 0, $batch_size);

    foreach ($batch as $attachment_id) {

        $result = yio_restore_attachment((int) $attachment_id);

        $state['processed']++;
        $state[$result['bucket']]++;

        $state['log'][] = $result['message'];

        if (count($state['log']) > 50) {
            array_shift($state['log']);
        }
    }

    set_transient('yio_restore_queue', $queue, 6 * HOUR_IN_SECONDS);

    if (empty($queue)) {
        yio_restore_finish($state);
    } else {
        yio_restore_state($state);
    }

    yio_restore_lock_release();

    return yio_restore_progress();
}

/*
|--------------------------------------------------------------------------
| Restore Single Attachment
|--------------------------------------------------------------------------
*/

/**
 * Restore one attachment from its backup, honoring restore_method.
 *
 * @param int $attachment_id
 * @return array {bucket, message}
 */
function yio_restore_attachment($attachment_id)
{
    $current = get_attached_file($attachment_id);

    if (!$current || !file_exists($current)) {
        return array('bucket' => 'failed', 'message' => '#' . $attachment_id . ': no attached file');
    }

    $backup = yio_find_backup_for($current);

    if (!$backup) {
        return array('bucket' => 'skipped', 'message' => '#' . $attachment_id . ': no backup found');
    }

    try {

        if (yio_get_option('restore_method', 'replace') === 'copy') {
            return yio_restore_as_new($backup, $attachment_id);
        }

        return yio_restore_in_place($backup, $current, $attachment_id);

    } catch (Throwable $e) {

        yio_log('Restore error on #' . $attachment_id . ': ' . $e->getMessage());

        return array('bucket' => 'failed', 'message' => '#' . $attachment_id . ': error — ' . $e->getMessage());
    }
}

/**
 * Locate the backup for a file by matching its relative path + base name
 * in the original-img directory (the extension changed after conversion,
 * so a glob over the supported originals is used).
 *
 * @param string $current_file Absolute path of the current attached file.
 * @return string|false
 */
function yio_find_backup_for($current_file)
{
    $relative = yio_relative_upload_path($current_file);

    if ($relative === '' || $relative === $current_file) {
        return false;
    }

    $dir  = dirname($relative);
    $base = pathinfo($relative, PATHINFO_FILENAME);

    // Glob the whole directory (not a pattern containing the file base,
    // which may hold glob metacharacters) and compare literal names.
    $search_dir = yio_original_dir() . ($dir !== '.' ? $dir . '/' : '');

    $matches = glob($search_dir . '*');

    if (empty($matches)) {
        return false;
    }

    $candidates = array();

    foreach ($matches as $match) {

        if (!is_file($match)) {
            continue;
        }

        $ext = strtolower(pathinfo($match, PATHINFO_EXTENSION));

        if (
            pathinfo($match, PATHINFO_FILENAME) === $base
            && in_array($ext, yio_supported_extensions(), true)
        ) {
            $candidates[] = $match;
        }
    }

    if (empty($candidates)) {
        return false;
    }

    // Prefer lossy/lossless originals over a WebP backup.
    usort($candidates, function ($a, $b) {

        $is_webp = function ($path) {
            return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp';
        };

        return (int) $is_webp($a) - (int) $is_webp($b);
    });

    return $candidates[0];
}

/**
 * Replace the optimized file with the original backup (restore_method =
 * "replace"). The backup file itself is removed once the original is live.
 *
 * @param string $backup
 * @param string $current
 * @param int    $attachment_id
 * @return array
 */
function yio_restore_in_place($backup, $current, $attachment_id)
{
    $dir      = dirname($current);
    $restored = $dir . '/' . basename($backup);

    if ($restored !== $current) {

        if (!@copy($backup, $restored)) {

            yio_log('Restore copy failed: ' . $backup . ' -> ' . $restored);

            return array('bucket' => 'failed', 'message' => '#' . $attachment_id . ': could not copy backup');
        }
    }

    // Pause the pipeline while the original is written back, so the
    // metadata regeneration below does not re-optimize it.
    yio_restore_active(true);

    try {

        // Regenerate the size variants from the restored original BEFORE
        // the optimized files are removed, so a failure here leaves the
        // current attachment fully intact (rollback-safe ordering).
        $new_metadata = wp_generate_attachment_metadata($attachment_id, $restored);

        if (!is_array($new_metadata)) {
            throw new RuntimeException('Metadata regeneration failed.');
        }

        // Remove the optimized size variants and the optimized file.
        $metadata = wp_get_attachment_metadata($attachment_id);

        if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {

            foreach ($metadata['sizes'] as $size) {

                if (empty($size['file'])) {
                    continue;
                }

                $size_file = $dir . '/' . $size['file'];

                if (is_file($size_file)) {
                    @unlink($size_file);
                }
            }
        }

        if (is_file($current) && $current !== $restored) {
            @unlink($current);
        }

        // Point WordPress at the restored file.
        update_attached_file($attachment_id, $restored);

        $filetype = wp_check_filetype($restored);

        wp_update_post(array(
            'ID'             => $attachment_id,
            'guid'           => wp_get_upload_dir()['baseurl'] . '/' . _wp_relative_upload_path($restored),
            'post_mime_type' => !empty($filetype['type']) ? $filetype['type'] : 'image/jpeg',
        ));

        wp_update_attachment_metadata($attachment_id, $new_metadata);

    } catch (Throwable $e) {

        // Rollback: remove the restored file and any regenerated sizes.
        if (isset($new_metadata) && is_array($new_metadata) && !empty($new_metadata['sizes'])) {

            foreach ($new_metadata['sizes'] as $size) {

                if (empty($size['file'])) {
                    continue;
                }

                $size_file = $dir . '/' . $size['file'];

                if (is_file($size_file)) {
                    @unlink($size_file);
                }
            }
        }

        if ($restored !== $current && is_file($restored)) {
            @unlink($restored);
        }

        throw $e;

    } finally {

        yio_restore_active(false);
    }

    // The original is live again, so its backup is no longer needed.
    @unlink($backup);

    return array('bucket' => 'restored', 'message' => '#' . $attachment_id . ': restored ' . basename($restored));
}

/**
 * Restore the backup as a brand-new attachment, keeping the optimized one
 * untouched (restore_method = "copy").
 *
 * @param string $backup
 * @param int    $original_id
 * @return array
 */
function yio_restore_as_new($backup, $original_id)
{
    $uploads  = wp_get_upload_dir();
    $relative = yio_relative_upload_path($backup);
    $dir      = dirname($relative);

    $name = wp_unique_filename(
        $uploads['basedir'] . '/' . ($dir !== '.' ? $dir : ''),
        'restored-' . basename($backup)
    );

    $new_path = $uploads['basedir'] . '/' . ($dir !== '.' ? $dir . '/' : '') . $name;

    if (!@copy($backup, $new_path)) {

        yio_log('Restore copy failed: ' . $backup . ' -> ' . $new_path);

        return array('bucket' => 'failed', 'message' => '#' . $original_id . ': could not copy backup');
    }

    $original = get_post($original_id);

    $filetype = wp_check_filetype($new_path);

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => !empty($filetype['type']) ? $filetype['type'] : 'image/jpeg',
        'post_title'     => $original ? $original->post_title : basename($name),
        'post_content'   => $original ? $original->post_content : '',
        'post_excerpt'   => $original ? $original->post_excerpt : '',
        'post_status'    => 'inherit',
    ), $new_path);

    if (is_wp_error($attachment_id) || !$attachment_id) {

        @unlink($new_path);

        return array('bucket' => 'failed', 'message' => '#' . $original_id . ': could not create attachment');
    }

    // Carry over the alt text.
    if ($original) {

        $alt = get_post_meta($original_id, '_wp_attachment_image_alt', true);

        if ($alt) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
        }
    }

    yio_restore_active(true);

    try {

        $metadata = wp_generate_attachment_metadata($attachment_id, $new_path);

        if (is_array($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

    } finally {

        yio_restore_active(false);
    }

    return array('bucket' => 'restored', 'message' => '#' . $original_id . ': restored as new attachment #' . $attachment_id);
}

/*
|--------------------------------------------------------------------------
| Scan
|--------------------------------------------------------------------------
*/

/**
 * All image attachment IDs that currently have a backup.
 *
 * @return int[]
 */
function yio_restore_scan()
{
    $query = new WP_Query(array(
        'post_type'              => 'attachment',
        'post_status'            => 'inherit',
        'post_mime_type'         => 'image/',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_term_cache' => false,
    ));

    $ids = array_filter($query->posts, function ($id) {

        $file = get_attached_file($id);

        return $file && yio_find_backup_for($file);
    });

    return array_values(array_unique(array_map('absint', $ids)));
}

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

function yio_restore_state($state = null)
{
    $key = 'yio_restore_state';

    if ($state === null) {
        return get_option($key, array());
    }

    update_option($key, $state);
}

function yio_restore_reset_state()
{
    delete_option('yio_restore_state');
    delete_transient('yio_restore_queue');
    delete_option('yio_restore_lock');
    wp_clear_scheduled_hook('yio_restore_cron');
}

function yio_restore_finish($state)
{
    $state['status']   = 'done';
    $state['finished'] = time();

    yio_restore_state($state);

    delete_transient('yio_restore_queue');
    wp_clear_scheduled_hook('yio_restore_cron');
}

/**
 * Progress snapshot for the UI.
 *
 * @return array
 */
function yio_restore_progress()
{
    $state = yio_restore_state();

    return array(
        'status'    => !empty($state['status']) ? $state['status'] : 'idle',
        'total'     => (int) ($state['total'] ?? 0),
        'processed' => (int) ($state['processed'] ?? 0),
        'restored'  => (int) ($state['restored'] ?? 0),
        'skipped'   => (int) ($state['skipped'] ?? 0),
        'failed'    => (int) ($state['failed'] ?? 0),
        'log'       => !empty($state['log']) ? array_slice($state['log'], -30) : array(),
    );
}

/*
|--------------------------------------------------------------------------
| Lock
|--------------------------------------------------------------------------
*/

/**
 * Atomic lock with stale-lock stealing.
 *
 * @return bool
 */
function yio_restore_lock_acquire()
{
    $lock = get_option('yio_restore_lock');

    if ($lock) {

        if (time() - (int) $lock > 15 * MINUTE_IN_SECONDS) {
            delete_option('yio_restore_lock');
        } else {
            return false;
        }
    }

    return add_option('yio_restore_lock', time());
}

function yio_restore_lock_release()
{
    delete_option('yio_restore_lock');
}
