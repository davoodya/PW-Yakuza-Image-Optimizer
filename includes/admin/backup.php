<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Backup Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_backup()
{

    $settings = yio_get_options();

    ?>

    <h2>Original Image Backup</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Backup Original Images
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[backup_original]"
                    value="1"

                    <?php checked(
                        $settings['backup_original'] ?? 0,
                        1
                    ); ?>

                >


                Keep original images before optimization


                <p class="description">

                    Original files will be stored in:
                    <br>

                    wp-content/uploads/original-img/

                </p>


            </td>


        </tr>



        <tr>

            <th scope="row">
                Backup Structure
            </th>


            <td>


                <code>
                original-img/YYYY/MM/
                </code>


                <p class="description">

                    Folder structure will match WordPress uploads.

                </p>


            </td>


        </tr>



    </table>



    <hr>



    <h2>Restore Options</h2>



    <table class="form-table">


        <tr>

            <th scope="row">
                Restore Method
            </th>


            <td>


                <select
                    id="yio-settings-restore-method"
                    name="yio_settings[restore_method]"
                >


                    <option
                        value="replace"
                        <?php selected(
                            $settings['restore_method'] ?? '',
                            'replace'
                        ); ?>
                    >

                        Replace optimized file

                    </option>



                    <option
                        value="copy"
                        <?php selected(
                            $settings['restore_method'] ?? '',
                            'copy'
                        ); ?>
                    >

                        Restore as new file

                    </option>


                </select>

                <p class="description">

                    "Replace" swaps the optimized file back to the original.
                    "Restore as new file" keeps the optimized image and adds the
                    original as a new attachment.

                </p>


            </td>


        </tr>



    </table>



    <hr>



    <h2>Image Comparison</h2>



    <table class="form-table">


        <tr>

            <th scope="row">
                Enable Comparison
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[image_comparison]"
                    value="1"

                    <?php checked(
                        $settings['image_comparison'] ?? 0,
                        1
                    ); ?>

                >


                Enable before / after comparison


            </td>


        </tr>


    </table>



    <hr>


    <h2>Restore Originals</h2>

    <p class="description">
        Restores every optimized image from its backup in
        <code>original-img/</code>, using the restore method selected above.
        The upload pipeline is paused during the restore, so restored
        originals are not re-optimized automatically.
    </p>

    <p>
        <button type="button" id="yio-restore-start" class="button button-primary button-hero">
            Restore All Originals
        </button>

        <button type="button" id="yio-restore-pause" class="button" style="display:none;">
            Pause
        </button>

        <button type="button" id="yio-restore-resume" class="button button-primary" style="display:none;">
            Resume
        </button>
    </p>

    <div id="yio-restore-status" class="yio-bulk-status">

        <div class="yio-bulk-progress">
            <div id="yio-restore-progress-bar" class="yio-bulk-progress-bar" style="width:0%"></div>
        </div>

        <p id="yio-restore-counter" class="description">Ready.</p>

    </div>

    <div id="yio-restore-summary" class="yio-bulk-summary" style="display:none;"></div>

    <div id="yio-restore-log" class="yio-bulk-log"></div>

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
    </style>

    <script>
    (function ($) {
        'use strict';

        var yioRestore = {
            url:     <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
            nonce:   <?php echo wp_json_encode(wp_create_nonce('yio_restore')); ?>,
            running: false
        };

        function post(action, data, cb) {
            $.post(yioRestore.url, $.extend({ action: action, nonce: yioRestore.nonce }, data), cb);
        }

        function logLine(msg) {
            var $log = $('#yio-restore-log');
            $log.append($('<div>').text(msg));
            $log.scrollTop($log[0].scrollHeight);
        }

        function render(progress) {
            var pct = progress.total > 0 ? Math.round(progress.processed / progress.total * 100) : 100;

            $('#yio-restore-progress-bar').css('width', pct + '%');

            $('#yio-restore-counter').text(
                progress.processed + ' of ' + progress.total + ' processed — ' +
                'restored ' + progress.restored + ', ' +
                'skipped ' + progress.skipped + ', ' +
                'failed ' + progress.failed
            );

            $('#yio-restore-log').empty();

            if (progress.log && progress.log.length) {
                $.each(progress.log, function (i, line) {
                    logLine(line);
                });
            }
        }

        function setMode(status) {
            $('#yio-restore-start').toggle(status === 'idle' || status === 'done');
            $('#yio-restore-pause').toggle(status === 'running');
            $('#yio-restore-resume').toggle(status === 'paused');
        }

        function showSummary(progress) {
            $('#yio-restore-summary').show().html(
                '<strong>Done.</strong> Restored ' + progress.restored + ', ' +
                'skipped ' + progress.skipped + ', failed ' + progress.failed + '.'
            );
        }

        function step() {
            if (!yioRestore.running) { return; }

            post('yio_restore_step', {}, function (res) {
                if (!res.success) {
                    yioRestore.running = false;
                    logLine('ERROR: ' + res.data);
                    setMode('idle');
                    return;
                }

                render(res.data);

                if (res.data.status === 'done') {
                    yioRestore.running = false;
                    setMode('done');
                    showSummary(res.data);
                    return;
                }

                setTimeout(step, res.data.busy ? 1500 : 100);
            });
        }

        function startRun() {
            if (yioRestore.running) { return; }

            var $btn = $('#yio-restore-start');
            yioRestore.running = true;
            $btn.prop('disabled', true);
            logLine('Starting...');

            post('yio_restore_start', { settings: { restore_method: $('#yio-settings-restore-method').val() } }, function (res) {
                $btn.prop('disabled', false);

                if (!res.success) {
                    yioRestore.running = false;
                    logLine('ERROR: ' + res.data);
                    setMode('idle');
                    return;
                }

                render(res.data);
                setMode(res.data.status);

                if (res.data.status === 'done') {
                    yioRestore.running = false;
                    showSummary(res.data);
                    return;
                }

                step();
            });
        }

        $(function () {
            $('#yio-restore-start').on('click', startRun);

            $('#yio-restore-resume').on('click', function () {
                if (yioRestore.running) { return; }

                yioRestore.running = true;
                logLine('Resuming...');

                post('yio_restore_start', { settings: { restore_method: $('#yio-settings-restore-method').val() } }, function (res) {
                    if (!res.success) {
                        yioRestore.running = false;
                        logLine('ERROR: ' + res.data);
                        return;
                    }

                    render(res.data);
                    setMode(res.data.status);

                    if (res.data.status === 'done') {
                        yioRestore.running = false;
                        showSummary(res.data);
                        return;
                    }

                    step();
                });
            });

            $('#yio-restore-pause').on('click', function () {
                post('yio_restore_cancel', {}, function (res) {
                    if (res.success) {
                        yioRestore.running = false;
                        setMode(res.data.status);
                        logLine('Paused. The queue is preserved — you can resume anytime.');
                    }
                });
            });

            // On load, pick up a run that is still active.
            post('yio_restore_status', {}, function (res) {
                if (!res.success) { return; }

                var p = res.data;

                if (p.status === 'running') {
                    render(p);
                    setMode('running');
                    yioRestore.running = true;
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
