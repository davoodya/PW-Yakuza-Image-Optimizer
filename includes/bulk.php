<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Bulk Optimizer Init
|--------------------------------------------------------------------------
*/

function yio_bulk_init()
{
    add_action('wp_ajax_yio_bulk_start', 'yio_bulk_ajax_start');
    add_action('wp_ajax_yio_bulk_step', 'yio_bulk_ajax_step');
    add_action('wp_ajax_yio_bulk_status', 'yio_bulk_ajax_status');
    add_action('wp_ajax_yio_bulk_cancel', 'yio_bulk_ajax_cancel');

    // Continuation for background processing (WP-Cron).
    add_action('yio_bulk_cron', 'yio_bulk_cron_run');
}

/*
|--------------------------------------------------------------------------
| AJAX: Start / Resume
|--------------------------------------------------------------------------
*/

function yio_bulk_ajax_start()
{
    check_ajax_referer('yio_bulk', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'yakuza-image-optimizer'));
    }

    if (!yio_get_option('enabled', 1)) {
        wp_send_json_error(__('The optimizer is disabled. Enable it in the General settings first.', 'yakuza-image-optimizer'));
    }

    // Which job are we running? "optimize" (default) or "watermark"
    // (the Apply-to-All run from the Watermark tab).
    $mode = (isset($_POST['mode']) && $_POST['mode'] === 'watermark') ? 'watermark' : 'optimize';

    // The run UI sits inside the settings form, so persist the current
    // field values (sanitized) together with the run request.
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {

        // A watermark run persists only the watermark fields exactly as
        // they appear in the form, even if the admin has not saved them
        // yet. Bulk/optimize runs persist only the bulk fields. Neither
        // may clobber the other tab's saved settings.
        $keys = ($mode === 'watermark')
            ? array(
                'watermark_enable',
                'watermark_image',
                'watermark_scale',
                'watermark_opacity',
                'watermark_position',
                'watermark_padding',
                'text_enable',
                'text_content',
                'text_size',
                'text_opacity',
                'text_position',
                'text_color',
            )
            : array('background_processing', 'bulk_batch_size', 'dry_run', 'bulk_limit', 'include_webp');

        $sanitized = yio_sanitize_settings(wp_unslash($_POST['settings']));

        update_option(
            'yio_settings',
            array_merge(yio_get_options(), array_intersect_key($sanitized, array_flip($keys)))
        );
    }

    // Resume support: reuse the queue of a paused run.
    $existing = yio_bulk_state();

    if (
        !empty($existing['status'])
        && $existing['status'] === 'paused'
        && get_transient('yio_bulk_queue')
        && (!empty($existing['mode']) ? $existing['mode'] : 'optimize') === $mode
    ) {

        $existing['status'] = 'running';

        yio_bulk_state($existing);

        yio_bulk_schedule_continuation();

        wp_send_json_success(yio_bulk_progress());
    }

    yio_bulk_reset_state();

    $ids = yio_bulk_scan($mode);

    if (empty($ids)) {

        wp_send_json_success(array(
            'status'    => 'done',
            'mode'      => $mode,
            'total'     => 0,
            'processed' => 0,
            'optimized' => 0,
            'skipped'   => 0,
            'failed'    => 0,
            'saved'     => 0,
            'dry_run'   => (int) yio_get_option('dry_run', 0),
            'log'       => array($mode === 'watermark'
                ? __('No images found to watermark.', 'yakuza-image-optimizer')
                : __('No images found to optimize.', 'yakuza-image-optimizer')),
        ));
    }

    set_transient('yio_bulk_queue', $ids, 6 * HOUR_IN_SECONDS);

    yio_bulk_state(array(
        'status'    => 'running',
        'mode'      => $mode,
        'total'     => count($ids),
        'processed' => 0,
        'optimized' => 0,
        'skipped'   => 0,
        'failed'    => 0,
        'saved'     => 0,
        'dry_run'   => (int) yio_get_option('dry_run', 0),
        'started'   => time(),
        'log'       => array(),
    ));

    yio_bulk_schedule_continuation();

    wp_send_json_success(yio_bulk_progress());
}

/*
|--------------------------------------------------------------------------
| AJAX: Step / Status / Cancel
|--------------------------------------------------------------------------
*/

function yio_bulk_ajax_step()
{
    check_ajax_referer('yio_bulk', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'yakuza-image-optimizer'));
    }

    wp_send_json_success(yio_bulk_process_batch());
}

function yio_bulk_ajax_status()
{
    check_ajax_referer('yio_bulk', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'yakuza-image-optimizer'));
    }

    wp_send_json_success(yio_bulk_progress());
}

function yio_bulk_ajax_cancel()
{
    check_ajax_referer('yio_bulk', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'yakuza-image-optimizer'));
    }

    $state = yio_bulk_state();

    if (!empty($state) && $state['status'] === 'running') {

        $state['status'] = 'paused';

        yio_bulk_state($state);
    }

    wp_send_json_success(yio_bulk_progress());
}

/*
|--------------------------------------------------------------------------
| Cron Continuation
|--------------------------------------------------------------------------
*/

function yio_bulk_cron_run()
{
    if (!yio_get_option('background_processing', 1)) {
        return;
    }

    $state = yio_bulk_state();

    if (empty($state) || $state['status'] !== 'running') {
        return;
    }

    yio_bulk_process_batch();

    // Keep the run alive until it finishes.
    $state = yio_bulk_state();

    if (!empty($state['status']) && $state['status'] === 'running') {
        wp_schedule_single_event(time() + 60, 'yio_bulk_cron');
    }
}

function yio_bulk_schedule_continuation()
{
    if (yio_get_option('background_processing', 1)) {
        wp_schedule_single_event(time() + 60, 'yio_bulk_cron');
    }
}

/*
|--------------------------------------------------------------------------
| Batch Processing
|--------------------------------------------------------------------------
*/

/**
 * Process one batch of attachments and return the progress snapshot.
 *
 * A short-lived lock prevents the AJAX driver and the cron continuation
 * from processing batches concurrently.
 *
 * @return array
 */
function yio_bulk_process_batch()
{
    wp_raise_memory_limit('image');

    if (function_exists('set_time_limit')) {
        @set_time_limit(300);
    }

    $state = yio_bulk_state();

    if (empty($state) || $state['status'] !== 'running') {
        return yio_bulk_progress();
    }

    if (!yio_bulk_lock_acquire()) {
        return array_merge(yio_bulk_progress(), array('busy' => true));
    }

    $queue = get_transient('yio_bulk_queue');

    if (empty($queue)) {

        yio_bulk_finish($state);

        yio_bulk_lock_release();

        return yio_bulk_progress();
    }

    $batch_size = min(100, max(1, (int) yio_get_option('bulk_batch_size', 20)));

    $batch = array_splice($queue, 0, $batch_size);

    $dry_run = (int) yio_get_option('dry_run', 0);

    $mode = !empty($state['mode']) ? $state['mode'] : 'optimize';

    foreach ($batch as $attachment_id) {

        $attachment_id = (int) $attachment_id;

        $result = yio_bulk_process_attachment($attachment_id, $dry_run, $mode);

        $state['processed']++;
        $state[$result['bucket']]++;
        $state['saved'] += (int) $result['saved'];

        $state['log'][] = $result['message'];

        if (count($state['log']) > 50) {
            array_shift($state['log']);
        }
    }

    set_transient('yio_bulk_queue', $queue, 6 * HOUR_IN_SECONDS);

    if (empty($queue)) {
        yio_bulk_finish($state);
    } else {
        yio_bulk_state($state);
    }

    yio_bulk_lock_release();

    return yio_bulk_progress();
}

/**
 * Optimize or watermark a single attachment for a bulk run.
 *
 * @param int    $attachment_id
 * @param int    $dry_run
 * @param string $mode 'optimize' or 'watermark'.
 * @return array {bucket, saved, message}
 */
function yio_bulk_process_attachment($attachment_id, $dry_run, $mode = 'optimize')
{
    // Watermark-only pass (Apply to All from the Watermark tab): the
    // existing file is kept in its current format and never converted.
    if ($mode === 'watermark') {

        if ($dry_run) {
            return array('bucket' => 'skipped', 'saved' => 0, 'message' => sprintf(
                __('#%1$d: [dry run] would watermark %2$s', 'yakuza-image-optimizer'),
                $attachment_id,
                basename(get_attached_file($attachment_id) ?: '')
            ));
        }

        $result = yio_apply_watermark_existing($attachment_id);

        // "watermarked" results are counted in the optimized bucket so
        // the shared state/progress schema (optimized/skipped/failed)
        // stays consistent across both run modes.
        return array(
            'bucket'  => $result['bucket'] === 'watermarked' ? 'optimized' : $result['bucket'],
            'saved'   => 0,
            'message' => $result['message'],
        );
    }

    $file = get_attached_file($attachment_id);

    if (!$file || !file_exists($file)) {
        return array('bucket' => 'failed', 'saved' => 0, 'message' => sprintf(
            __('#%1$d: file missing on disk', 'yakuza-image-optimizer'),
            $attachment_id
        ));
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($ext === 'gif' && yio_is_animated_gif($file)) {
        return array('bucket' => 'skipped', 'saved' => 0, 'message' => sprintf(
            __('#%1$d: animated GIF skipped', 'yakuza-image-optimizer'),
            $attachment_id
        ));
    }

    $target = yio_output_extension();

    if (!yio_get_option('include_webp', 0) && $ext === $target) {
        return array('bucket' => 'skipped', 'saved' => 0, 'message' => sprintf(
            __('#%1$d: already %2$s', 'yakuza-image-optimizer'),
            $attachment_id,
            strtoupper($target)
        ));
    }

    $metadata = wp_get_attachment_metadata($attachment_id);

    if (!is_array($metadata)) {
        return array('bucket' => 'skipped', 'saved' => 0, 'message' => sprintf(
            __('#%1$d: no image metadata', 'yakuza-image-optimizer'),
            $attachment_id
        ));
    }

    if ($dry_run) {

        yio_log('DRY RUN: ' . $file . ' -> .' . $target);

        return array('bucket' => 'skipped', 'saved' => 0, 'message' => sprintf(
            __('#%1$d: [dry run] would optimize %2$s', 'yakuza-image-optimizer'),
            $attachment_id,
            basename($file)
        ));
    }

    try {

        $old_size = (int) filesize($file);

        $new_metadata = yio_optimize_single_image($file, $metadata, $attachment_id);

        if ($new_metadata === $metadata) {
            return array('bucket' => 'skipped', 'saved' => 0, 'message' => sprintf(
                __('#%1$d: unchanged (%2$s)', 'yakuza-image-optimizer'),
                $attachment_id,
                basename($file)
            ));
        }

        // In the upload flow WordPress stores the metadata; here we must.
        wp_update_attachment_metadata($attachment_id, $new_metadata);

        $saved = 0;

        $new_file = dirname($file) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.' . $target;

        if (is_file($new_file)) {
            $saved = max(0, $old_size - (int) filesize($new_file));
        }

        return array(
            'bucket'  => 'optimized',
            'saved'   => $saved,
            'message' => sprintf(
                __('#%1$d: optimized %2$s', 'yakuza-image-optimizer'),
                $attachment_id,
                basename($file)
            ),
        );

    } catch (Throwable $e) {

        yio_log('Bulk error on #' . $attachment_id . ': ' . $e->getMessage());

        return array('bucket' => 'failed', 'saved' => 0, 'message' => sprintf(
            __('#%1$d: error — %2$s', 'yakuza-image-optimizer'),
            $attachment_id,
            $e->getMessage()
        ));
    }
}

/*
|--------------------------------------------------------------------------
| Scan
|--------------------------------------------------------------------------
*/

/**
 * All candidate attachment IDs, honoring bulk_limit and include_webp.
 * In watermark mode every image is a candidate (no format filtering).
 *
 * @param string $mode 'optimize' or 'watermark'.
 * @return int[]
 */
function yio_bulk_scan($mode = 'optimize')
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

    $ids = array_map('absint', $query->posts);

    $limit = (int) yio_get_option('bulk_limit', 0);

    if ($limit > 0) {
        $ids = array_slice($ids, 0, $limit);
    }

    // When include_webp is off, drop files already in the target format.
    if ($mode !== 'watermark' && !yio_get_option('include_webp', 0)) {

        $target = yio_output_extension();

        $ids = array_values(array_filter($ids, function ($id) use ($target) {

            $file = get_attached_file($id);

            return $file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== $target;
        }));
    }

    return array_values(array_unique($ids));
}

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

function yio_bulk_state($state = null)
{
    $key = 'yio_bulk_state';

    if ($state === null) {
        return get_option($key, array());
    }

    update_option($key, $state);
}

function yio_bulk_reset_state()
{
    delete_option('yio_bulk_state');
    delete_transient('yio_bulk_queue');
    delete_option('yio_bulk_lock');
    wp_clear_scheduled_hook('yio_bulk_cron');
}

function yio_bulk_finish($state)
{
    $state['status']   = 'done';
    $state['finished'] = time();

    yio_bulk_state($state);

    delete_transient('yio_bulk_queue');
    wp_clear_scheduled_hook('yio_bulk_cron');
}

/**
 * Progress snapshot for the UI.
 *
 * @return array
 */
function yio_bulk_progress()
{
    $state = yio_bulk_state();

    return array(
        'status'    => !empty($state['status']) ? $state['status'] : 'idle',
        'mode'      => !empty($state['mode']) ? $state['mode'] : 'optimize',
        'total'     => (int) ($state['total'] ?? 0),
        'processed' => (int) ($state['processed'] ?? 0),
        'optimized' => (int) ($state['optimized'] ?? 0),
        'skipped'   => (int) ($state['skipped'] ?? 0),
        'failed'    => (int) ($state['failed'] ?? 0),
        'saved'     => (int) ($state['saved'] ?? 0),
        'dry_run'   => (int) ($state['dry_run'] ?? 0),
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
function yio_bulk_lock_acquire()
{
    $lock = get_option('yio_bulk_lock');

    if ($lock) {

        if (time() - (int) $lock > 15 * MINUTE_IN_SECONDS) {
            delete_option('yio_bulk_lock');
        } else {
            return false;
        }
    }

    return add_option('yio_bulk_lock', time());
}

function yio_bulk_lock_release()
{
    delete_option('yio_bulk_lock');
}
