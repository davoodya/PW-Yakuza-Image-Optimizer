<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Bulk Optimizer Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_bulk()
{

    $settings = yio_get_options();

    $count_query = new WP_Query(array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image/',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ));

    $image_count = (int) $count_query->found_posts;

    $yio_i18n = [

        'of'           => __('of', 'yakuza-image-optimizer'),
        'processed'    => __('processed', 'yakuza-image-optimizer'),
        'optimized'    => __('optimized', 'yakuza-image-optimizer'),
        'skipped'      => __('skipped', 'yakuza-image-optimizer'),
        'failed'       => __('failed', 'yakuza-image-optimizer'),
        'starting'     => __('Starting...', 'yakuza-image-optimizer'),
        'resuming'     => __('Resuming...', 'yakuza-image-optimizer'),
        'error'        => __('ERROR:', 'yakuza-image-optimizer'),
        'paused'       => __('Paused. The queue is preserved — you can resume anytime.', 'yakuza-image-optimizer'),
        'done'         => __('Done.', 'yakuza-image-optimizer'),
        'dry_run_note' => __('(DRY RUN — no files are changed)', 'yakuza-image-optimizer'),
        'total_saved'  => __('Total saved:', 'yakuza-image-optimizer'),

    ];

    ?>

    <h2><?php echo esc_html__('Bulk Image Optimization', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Background Processing', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="checkbox"
                    id="yio-settings-background"
                    name="yio_settings[background_processing]"
                    value="1"

                    <?php checked(
                        $settings['background_processing'] ?? 0,
                        1
                    ); ?>

                >

                <?php echo esc_html__('Enable background processing', 'yakuza-image-optimizer'); ?>


                <p class="description">

                    <?php echo esc_html__('Images will be processed in small batches to reduce server load. When enabled, batches continue via WP-Cron even if you leave this page.', 'yakuza-image-optimizer'); ?>

                </p>


            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Batch Size', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    id="yio-settings-batch"
                    min="1"
                    max="100"
                    name="yio_settings[bulk_batch_size]"
                    value="<?php echo esc_attr(
                        $settings['bulk_batch_size'] ?? 20
                    ); ?>"
                >


                <?php echo esc_html__('images per batch', 'yakuza-image-optimizer'); ?>


                <p class="description">

                    <?php echo esc_html__('Recommended: 10-30 for shared hosting.', 'yakuza-image-optimizer'); ?>

                </p>


            </td>

        </tr>



    </table>



    <hr>



    <h2><?php echo esc_html__('Optimization Mode', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Dry Run', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="checkbox"
                    id="yio-settings-dryrun"
                    name="yio_settings[dry_run]"
                    value="1"

                    <?php checked(
                        $settings['dry_run'] ?? 0,
                        1
                    ); ?>

                >


                <?php echo esc_html__('Only scan and report files without changing them.', 'yakuza-image-optimizer'); ?>


                <p class="description">

                    <?php echo esc_html__('Recommended before first bulk optimization.', 'yakuza-image-optimizer'); ?>

                </p>


            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Processing Limit', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    id="yio-settings-limit"
                    min="0"
                    max="100000"
                    name="yio_settings[bulk_limit]"
                    value="<?php echo esc_attr(
                        $settings['bulk_limit'] ?? 0
                    ); ?>"
                >


                <p class="description">

                    <?php echo esc_html__('0 = Process all images.', 'yakuza-image-optimizer'); ?>

                </p>


            </td>

        </tr>


    </table>



    <hr>



    <h2><?php echo esc_html__('Regenerate Options', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Include Existing WebP', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="checkbox"
                    id="yio-settings-webp"
                    name="yio_settings[include_webp]"
                    value="1"

                    <?php checked(
                        $settings['include_webp'] ?? 0,
                        1
                    ); ?>

                >


                <?php echo esc_html__('Re-optimize existing WebP files.', 'yakuza-image-optimizer'); ?>


            </td>

        </tr>

    </table>



    <hr>


    <h2><?php echo esc_html__('Run Bulk Optimization', 'yakuza-image-optimizer'); ?></h2>

    <p class="description">
        <?php

        echo esc_html(
            sprintf(
                /* translators: %s: number of images found in the library. */
                __('Scans the Media Library (%s images found) and applies the same pipeline as new uploads: backup, auto-orient, smart resize, watermark, SEO uniqueness and WebP/AVIF conversion.', 'yakuza-image-optimizer'),
                number_format_i18n($image_count)
            )
        );

        ?>
    </p>

    <p>
        <button type="button" id="yio-bulk-start" class="button button-primary button-hero">
            <?php echo esc_html__('Start Bulk Optimization', 'yakuza-image-optimizer'); ?>
        </button>

        <button type="button" id="yio-bulk-pause" class="button" style="display:none;">
            <?php echo esc_html__('Pause', 'yakuza-image-optimizer'); ?>
        </button>

        <button type="button" id="yio-bulk-resume" class="button button-primary" style="display:none;">
            <?php echo esc_html__('Resume', 'yakuza-image-optimizer'); ?>
        </button>
    </p>

    <div id="yio-bulk-status" class="yio-bulk-status">

        <div class="yio-bulk-progress">
            <div id="yio-bulk-progress-bar" class="yio-bulk-progress-bar" style="width:0%"></div>
        </div>

        <p id="yio-bulk-counter" class="description"><?php echo esc_html__('Ready.', 'yakuza-image-optimizer'); ?></p>

    </div>

    <div id="yio-bulk-summary" class="yio-bulk-summary" style="display:none;"></div>

    <div id="yio-bulk-log" class="yio-bulk-log"></div>

    <style>
        .yio-bulk-progress {
            background: #e5e5e5;
            height: 18px;
            border-radius: 9px;
            overflow: hidden;
            max-width: 600px;
            margin: 8px 0;
        }
        .yio-bulk-progress-bar {
            background: #2271b1;
            height: 100%;
            width: 0;
            transition: width .3s ease;
        }
        .yio-bulk-log {
            background: #1d2327;
            color: #dcdcde;
            padding: 12px 14px;
            max-height: 300px;
            overflow: auto;
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
            line-height: 1.6;
            border-radius: 4px;
            max-width: 600px;
        }
        .yio-bulk-summary {
            background: #f0f6fc;
            border: 1px solid #c5d9ed;
            padding: 12px 14px;
            border-radius: 4px;
            max-width: 600px;
            margin: 10px 0;
        }
        [dir="rtl"] .yio-bulk-progress {
            direction: rtl;
        }
        [dir="rtl"] .yio-bulk-log {
            direction: rtl;
            text-align: right;
        }
    </style>

    <script>
    (function ($) {
        'use strict';

        var yioBulk = {
            url:      <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
            nonce:    <?php echo wp_json_encode(wp_create_nonce('yio_bulk')); ?>,
            running:  false
        };

        var i18n = <?php echo wp_json_encode($yio_i18n); ?>;

        function settingsPayload() {
            return {
                background_processing: $('#yio-settings-background').is(':checked') ? 1 : 0,
                bulk_batch_size:       $('#yio-settings-batch').val(),
                dry_run:               $('#yio-settings-dryrun').is(':checked') ? 1 : 0,
                bulk_limit:            $('#yio-settings-limit').val(),
                include_webp:          $('#yio-settings-webp').is(':checked') ? 1 : 0
            };
        }

        function post(action, data, cb) {
            $.post(yioBulk.url, $.extend({ action: action, nonce: yioBulk.nonce }, data), cb);
        }

        function logLine(msg) {
            var $log = $('#yio-bulk-log');
            $log.append($('<div>').text(msg));
            $log.scrollTop($log[0].scrollHeight);
        }

        function formatBytes(bytes) {
            if (bytes < 1024) { return bytes + ' B'; }
            if (bytes < 1048576) { return (bytes / 1024).toFixed(1) + ' KB'; }
            return (bytes / 1048576).toFixed(2) + ' MB';
        }

        function render(progress) {
            var pct = progress.total > 0 ? Math.round(progress.processed / progress.total * 100) : 100;

            $('#yio-bulk-progress-bar').css('width', pct + '%');

            $('#yio-bulk-counter').text(
                progress.processed + ' ' + i18n.of + ' ' + progress.total + ' ' + i18n.processed + ' — ' +
                i18n.optimized + ' ' + progress.optimized + '، ' +
                i18n.skipped + ' ' + progress.skipped + '، ' +
                i18n.failed + ' ' + progress.failed +
                (progress.dry_run ? ' ' + i18n.dry_run_note : '')
            );

            $('#yio-bulk-log').empty();

            if (progress.log && progress.log.length) {
                $.each(progress.log, function (i, line) {
                    logLine(line);
                });
            }
        }

        function setMode(status) {
            $('#yio-bulk-start').toggle(status === 'idle' || status === 'done');
            $('#yio-bulk-pause').toggle(status === 'running');
            $('#yio-bulk-resume').toggle(status === 'paused');
        }

        function showSummary(progress) {
            var text = '<strong>' + i18n.done + '</strong> ' +
                i18n.optimized + ' ' + progress.optimized + '، ' +
                i18n.skipped + ' ' + progress.skipped + '، ' +
                i18n.failed + ' ' + progress.failed + '.';

            if (progress.saved > 0 && !progress.dry_run) {
                text += ' ' + i18n.total_saved + ' ' + formatBytes(progress.saved) + '.';
            }

            $('#yio-bulk-summary').show().html(text);
        }

        function step() {
            if (!yioBulk.running) { return; }

            post('yio_bulk_step', {}, function (res) {
                if (!res.success) {
                    yioBulk.running = false;
                    logLine(i18n.error + ' ' + res.data);
                    setMode('idle');
                    return;
                }

                render(res.data);

                if (res.data.status === 'done') {
                    yioBulk.running = false;
                    setMode('done');
                    showSummary(res.data);
                    return;
                }

                // Busy means another driver (cron) holds the batch lock.
                setTimeout(step, res.data.busy ? 1500 : 100);
            });
        }

        function startRun() {
            if (yioBulk.running) { return; }

            var $btn = $('#yio-bulk-start');
            yioBulk.running = true;
            $btn.prop('disabled', true);
            logLine(i18n.starting);

            post('yio_bulk_start', { settings: settingsPayload() }, function (res) {
                $btn.prop('disabled', false);

                if (!res.success) {
                    yioBulk.running = false;
                    logLine(i18n.error + ' ' + res.data);
                    setMode('idle');
                    return;
                }

                render(res.data);
                setMode(res.data.status);

                if (res.data.status === 'done') {
                    yioBulk.running = false;
                    showSummary(res.data);
                    return;
                }

                step();
            });
        }

        function resumeRun() {
            if (yioBulk.running) { return; }

            yioBulk.running = true;
            logLine(i18n.resuming);

            post('yio_bulk_start', { settings: settingsPayload() }, function (res) {
                if (!res.success) {
                    yioBulk.running = false;
                    logLine(i18n.error + ' ' + res.data);
                    return;
                }

                render(res.data);
                setMode(res.data.status);

                if (res.data.status === 'done') {
                    yioBulk.running = false;
                    showSummary(res.data);
                    return;
                }

                step();
            });
        }

        $(function () {
            $('#yio-bulk-start').on('click', startRun);
            $('#yio-bulk-resume').on('click', resumeRun);

            $('#yio-bulk-pause').on('click', function () {
                post('yio_bulk_cancel', {}, function (res) {
                    if (res.success) {
                        yioBulk.running = false;
                        setMode(res.data.status);
                        logLine(i18n.paused);
                    }
                });
            });

            // On load, pick up a run that is still active (e.g. after a
            // page reload or when cron continued it in the background).
            post('yio_bulk_status', {}, function (res) {
                if (!res.success) { return; }

                var p = res.data;

                if (p.status === 'running') {
                    render(p);
                    setMode('running');
                    yioBulk.running = true;
                    step();
                } else if (p.status === 'paused') {
                    render(p);
                    setMode('paused');
                } else if (p.status === 'done') {
                    render(p);
                    setMode('done');
                    showSummary(p);
                } else {
                    setMode('idle');
                }
            });
        });

    })(jQuery);
    </script>

    <?php

}
