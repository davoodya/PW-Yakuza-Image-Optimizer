<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Watermark Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_watermark()
{

    $settings = yio_get_options();

    $watermark_id = (int) ($settings['watermark_image'] ?? 0);

    // Existing selection preview, shown until the admin picks a new one.
    $wm_preview = '';

    if ($watermark_id) {

        $src = wp_get_attachment_image_src($watermark_id, 'thumbnail');

        if ($src) {
            $wm_preview = '<img src="' . esc_url($src[0]) . '" class="yio-wm-thumb" alt="">';
        }
    }

    // The stored color may be empty/invalid; the color input needs #rrggbb.
    $text_color = (string) ($settings['text_color'] ?? '#FFFFFF');

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $text_color)) {
        $text_color = '#FFFFFF';
    }

    $yio_i18n = [

        'of'           => __('of', 'yakuza-image-optimizer'),
        'processed'    => __('processed', 'yakuza-image-optimizer'),
        'watermarked'  => __('watermarked', 'yakuza-image-optimizer'),
        'skipped'      => __('skipped', 'yakuza-image-optimizer'),
        'failed'       => __('failed', 'yakuza-image-optimizer'),
        'starting'     => __('Starting...', 'yakuza-image-optimizer'),
        'resuming'     => __('Resuming...', 'yakuza-image-optimizer'),
        'error'        => __('ERROR:', 'yakuza-image-optimizer'),
        'paused'       => __('Paused. The queue is preserved — you can resume anytime.', 'yakuza-image-optimizer'),
        'done'         => __('Done.', 'yakuza-image-optimizer'),
        'ready'        => __('Ready.', 'yakuza-image-optimizer'),
        'select_image' => __('Select Image', 'yakuza-image-optimizer'),
        'remove'       => __('Remove', 'yakuza-image-optimizer'),
        'dry_run_note' => __('(DRY RUN — no files are changed)', 'yakuza-image-optimizer'),

    ];

    ?>

    <h2><?php echo esc_html__('Image Watermark', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Enable Image Watermark', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <label>

                    <input
                        type="checkbox"
                        name="yio_settings[watermark_enable]"
                        value="1"
                        <?php checked(
                            $settings['watermark_enable'] ?? 0,
                            1
                        ); ?>
                    >

                    <?php echo esc_html__('Enable image watermark', 'yakuza-image-optimizer'); ?>

                </label>

            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Watermark Image', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <div class="yio-wm-picker">

                    <div id="yio-wm-preview" class="yio-wm-preview">
                        <?php echo $wm_preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url above. ?>
                    </div>

                    <p>
                        <button type="button" id="yio-wm-select" class="button">
                            <?php echo esc_html__('Select Image', 'yakuza-image-optimizer'); ?>
                        </button>

                        <button type="button" id="yio-wm-remove" class="button" <?php echo $watermark_id ? '' : 'style="display:none;"'; ?>>
                            <?php echo esc_html__('Remove', 'yakuza-image-optimizer'); ?>
                        </button>
                    </p>

                    <input
                        type="hidden"
                        id="yio-wm-image-id"
                        name="yio_settings[watermark_image]"
                        value="<?php echo esc_attr($watermark_id); ?>"
                    >

                </div>

                <p class="description">
                    <?php echo esc_html__('Choose the watermark image from the Media Library.', 'yakuza-image-optimizer'); ?>
                </p>


            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Watermark Scale (%)', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="5"
                    max="100"
                    name="yio_settings[watermark_scale]"
                    value="<?php echo esc_attr(
                        $settings['watermark_scale'] ?? 33
                    ); ?>"
                >

            </td>

        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Watermark Opacity', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="1"
                    max="100"
                    name="yio_settings[watermark_opacity]"
                    value="<?php echo esc_attr(
                        $settings['watermark_opacity'] ?? 60
                    ); ?>"
                >

            </td>

        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Position', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <select name="yio_settings[watermark_position]">


                    <?php

                    $positions = [

                        'top-left'      => __('Top Left', 'yakuza-image-optimizer'),
                        'top-center'    => __('Top Center', 'yakuza-image-optimizer'),
                        'top-right'     => __('Top Right', 'yakuza-image-optimizer'),

                        'center-left'   => __('Center Left', 'yakuza-image-optimizer'),
                        'center'        => __('Center', 'yakuza-image-optimizer'),
                        'center-right'  => __('Center Right', 'yakuza-image-optimizer'),

                        'bottom-left'   => __('Bottom Left', 'yakuza-image-optimizer'),
                        'bottom-center' => __('Bottom Center', 'yakuza-image-optimizer'),
                        'bottom-right'  => __('Bottom Right', 'yakuza-image-optimizer'),

                    ];


                    foreach ($positions as $key => $label):

                    ?>


                    <option
                        value="<?php echo esc_attr($key); ?>"
                        <?php selected(
                            $settings['watermark_position'] ?? '',
                            $key
                        ); ?>
                    >

                        <?php echo esc_html($label); ?>

                    </option>


                    <?php endforeach; ?>


                </select>


            </td>

        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Padding', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="0"
                    max="200"
                    name="yio_settings[watermark_padding]"
                    value="<?php echo esc_attr(
                        $settings['watermark_padding'] ?? 5
                    ); ?>"
                >

            </td>

        </tr>


    </table>



    <hr>


    <h2><?php echo esc_html__('Text Watermark', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Enable Text Watermark', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[text_enable]"
                    value="1"
                    <?php checked(
                        $settings['text_enable'] ?? 0,
                        1
                    ); ?>
                >


                <?php echo esc_html__('Enable text watermark', 'yakuza-image-optimizer'); ?>


            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Text', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="text"
                    class="regular-text"
                    name="yio_settings[text_content]"
                    value="<?php echo esc_attr(
                        $settings['text_content'] ?? ''
                    ); ?>"
                >


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Font Size', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="8"
                    max="200"
                    name="yio_settings[text_size]"
                    value="<?php echo esc_attr(
                        $settings['text_size'] ?? 22
                    ); ?>"
                >


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Text Color', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="color"
                    id="yio-text-color"
                    name="yio_settings[text_color]"
                    value="<?php echo esc_attr($text_color); ?>"
                >


                <p class="description">
                    <?php echo esc_html__('Pick the text color from the color palette.', 'yakuza-image-optimizer'); ?>
                </p>


            </td>


        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Text Opacity', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="1"
                    max="100"
                    name="yio_settings[text_opacity]"
                    value="<?php echo esc_attr(
                        $settings['text_opacity'] ?? 90
                    ); ?>"
                >


            </td>


        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Text Position', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <select name="yio_settings[text_position]">


                <?php foreach ($positions as $key => $label): ?>


                    <option
                        value="<?php echo esc_attr($key); ?>"
                        <?php selected(
                            $settings['text_position'] ?? '',
                            $key
                        ); ?>
                    >

                        <?php echo esc_html($label); ?>


                    </option>


                <?php endforeach; ?>


                </select>


            </td>


        </tr>



    </table>



    <hr>


    <h2><?php echo esc_html__('Apply Watermark to Existing Images', 'yakuza-image-optimizer'); ?></h2>

    <p class="description">
        <?php echo esc_html__('Existing images are never watermarked automatically. Use the button below to apply the watermark to every image in the library now. Images keep their current format — no conversion happens.', 'yakuza-image-optimizer'); ?>
    </p>

    <p>
        <button type="button" id="yio-wm-start" class="button button-primary button-hero">
            <?php echo esc_html__('Apply to All', 'yakuza-image-optimizer'); ?>
        </button>

        <button type="button" id="yio-wm-pause" class="button" style="display:none;">
            <?php echo esc_html__('Pause', 'yakuza-image-optimizer'); ?>
        </button>

        <button type="button" id="yio-wm-resume" class="button button-primary" style="display:none;">
            <?php echo esc_html__('Resume', 'yakuza-image-optimizer'); ?>
        </button>
    </p>

    <div id="yio-wm-status" class="yio-bulk-status">

        <div class="yio-bulk-progress">
            <div id="yio-wm-progress-bar" class="yio-bulk-progress-bar" style="width:0%"></div>
        </div>

        <p id="yio-wm-counter" class="description"><?php echo esc_html__('Ready.', 'yakuza-image-optimizer'); ?></p>

    </div>

    <div id="yio-wm-summary" class="yio-bulk-summary" style="display:none;"></div>

    <div id="yio-wm-log" class="yio-bulk-log"></div>

    <style>
        .yio-wm-preview {
            min-height: 60px;
            margin-bottom: 6px;
        }
        .yio-wm-thumb {
            max-width: 120px;
            max-height: 120px;
            height: auto;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 2px;
            background: #fff;
        }
        .yio-wm-picker input[type="color"] {
            vertical-align: middle;
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

        var yioWm = {
            url:     <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
            nonce:   <?php echo wp_json_encode(wp_create_nonce('yio_bulk')); ?>,
            running: false
        };

        var i18n = <?php echo wp_json_encode($yio_i18n); ?>;

        function post(action, data, cb) {
            $.post(yioWm.url, $.extend({ action: action, nonce: yioWm.nonce }, data), cb);
        }

        function logLine(msg) {
            var $log = $('#yio-wm-log');
            $log.append($('<div>').text(msg));
            $log.scrollTop($log[0].scrollHeight);
        }

        function render(progress) {
            var pct = progress.total > 0 ? Math.round(progress.processed / progress.total * 100) : 100;

            $('#yio-wm-progress-bar').css('width', pct + '%');

            $('#yio-wm-counter').text(
                progress.processed + ' ' + i18n.of + ' ' + progress.total + ' ' + i18n.processed + ' — ' +
                i18n.watermarked + ' ' + progress.optimized + '، ' +
                i18n.skipped + ' ' + progress.skipped + '، ' +
                i18n.failed + ' ' + progress.failed +
                (progress.dry_run ? ' ' + i18n.dry_run_note : '')
            );

            $('#yio-wm-log').empty();

            if (progress.log && progress.log.length) {
                $.each(progress.log, function (i, line) {
                    logLine(line);
                });
            }
        }

        function setMode(status) {
            $('#yio-wm-start').toggle(status === 'idle' || status === 'done');
            $('#yio-wm-pause').toggle(status === 'running');
            $('#yio-wm-resume').toggle(status === 'paused');
        }

        function showSummary(progress) {
            var text = '<strong>' + i18n.done + '</strong> ' +
                i18n.watermarked + ' ' + progress.optimized + '، ' +
                i18n.skipped + ' ' + progress.skipped + '، ' +
                i18n.failed + ' ' + progress.failed + '.';

            $('#yio-wm-summary').show().html(text);
        }

        function step() {
            if (!yioWm.running) { return; }

            post('yio_bulk_step', {}, function (res) {
                if (!res.success) {
                    yioWm.running = false;
                    logLine(i18n.error + ' ' + res.data);
                    setMode('idle');
                    return;
                }

                render(res.data);

                if (res.data.status === 'done') {
                    yioWm.running = false;
                    setMode('done');
                    showSummary(res.data);
                    return;
                }

                setTimeout(step, res.data.busy ? 1500 : 100);
            });
        }

        function startRun() {
            if (yioWm.running) { return; }

            var $btn = $('#yio-wm-start');
            yioWm.running = true;
            $btn.prop('disabled', true);
            logLine(i18n.starting);

            post('yio_bulk_start', { settings: watermarkSettings(), mode: 'watermark' }, function (res) {
                $btn.prop('disabled', false);

                if (!res.success) {
                    yioWm.running = false;
                    logLine(i18n.error + ' ' + res.data);
                    setMode('idle');
                    return;
                }

                render(res.data);
                setMode(res.data.status);

                if (res.data.status === 'done') {
                    yioWm.running = false;
                    showSummary(res.data);
                    return;
                }

                step();
            });
        }

        function resumeRun() {
            if (yioWm.running) { return; }

            yioWm.running = true;
            logLine(i18n.resuming);

            post('yio_bulk_start', { settings: watermarkSettings(), mode: 'watermark' }, function (res) {
                if (!res.success) {
                    yioWm.running = false;
                    logLine(i18n.error + ' ' + res.data);
                    return;
                }

                render(res.data);
                setMode(res.data.status);

                if (res.data.status === 'done') {
                    yioWm.running = false;
                    showSummary(res.data);
                    return;
                }

                step();
            });
        }

        function watermarkSettings() {
            return {
                watermark_enable:   $('input[name="yio_settings[watermark_enable]"]').is(':checked') ? 1 : 0,
                watermark_image:    $('#yio-wm-image-id').val(),
                watermark_scale:    $('input[name="yio_settings[watermark_scale]"]').val(),
                watermark_opacity:  $('input[name="yio_settings[watermark_opacity]"]').val(),
                watermark_position: $('select[name="yio_settings[watermark_position]"]').val(),
                watermark_padding:  $('input[name="yio_settings[watermark_padding]"]').val(),
                text_enable:        $('input[name="yio_settings[text_enable]"]').is(':checked') ? 1 : 0,
                text_content:       $('input[name="yio_settings[text_content]"]').val(),
                text_size:          $('input[name="yio_settings[text_size]"]').val(),
                text_opacity:       $('input[name="yio_settings[text_opacity]"]').val(),
                text_position:      $('select[name="yio_settings[text_position]"]').val(),
                text_color:         $('#yio-text-color').val()
            };
        }

        // Media Library picker for the watermark image.
        var wmFrame = null;

        function openWmPicker() {
            if (wmFrame) {
                wmFrame.open();
                return;
            }

            wmFrame = wp.media({
                title: i18n.select_image,
                button: { text: i18n.select_image },
                library: { type: 'image' },
                multiple: false
            });

            wmFrame.on('select', function () {
                var att = wmFrame.state().get('selection').first().toJSON();

                $('#yio-wm-image-id').val(att.id);
                $('#yio-wm-preview').html(
                    '<img src="' + (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) + '" class="yio-wm-thumb" alt="">'
                );
                $('#yio-wm-remove').show();
            });

            wmFrame.open();
        }

        $(function () {
            $('#yio-wm-select').on('click', function (e) {
                e.preventDefault();
                openWmPicker();
            });

            $('#yio-wm-remove').on('click', function () {
                $('#yio-wm-image-id').val('');
                $('#yio-wm-preview').empty();
                $(this).hide();
            });

            $('#yio-wm-start').on('click', startRun);
            $('#yio-wm-resume').on('click', resumeRun);

            $('#yio-wm-pause').on('click', function () {
                post('yio_bulk_cancel', {}, function (res) {
                    if (res.success) {
                        yioWm.running = false;
                        setMode(res.data.status);
                        logLine(i18n.paused);
                    }
                });
            });

            // On load, pick up a watermark-mode run that is still active.
            post('yio_bulk_status', {}, function (res) {
                if (!res.success) { return; }

                var p = res.data;

                if (p.mode && p.mode !== 'watermark') {
                    setMode('idle');
                    return;
                }

                if (p.status === 'running') {
                    render(p);
                    setMode('running');
                    yioWm.running = true;
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
