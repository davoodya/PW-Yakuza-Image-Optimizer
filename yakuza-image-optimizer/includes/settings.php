<?php
if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Register Settings
|--------------------------------------------------------------------------
*/

function yio_settings_init()
{
    register_setting(
        'yio_settings_group',
        'yio_settings',
        [
            'sanitize_callback' => 'yio_sanitize_settings',
            'default' => yio_default_settings()
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Default Settings
|--------------------------------------------------------------------------
*/

function yio_default_settings()
{
    return [

        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */

        'enabled' => 1,

        'backup_original' => 1,
		
		'restore_method' => 'replace',

		'image_comparison' => 1,

        'process_new_uploads' => 1,

        'output_format' => 'webp',

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        'image_quality' => 80,

        'smart_resize' => 1,

        'max_width' => 2000,

        'auto_orientation' => 1,

        /*
        |--------------------------------------------------------------------------
        | Watermark
        |--------------------------------------------------------------------------
        */

        'watermark_enable' => 1,

        'watermark_image' => '',

        'watermark_scale' => 33,

        'watermark_opacity' => 60,

        'watermark_position' => 'bottom-left',

        'watermark_padding' => 5,

        /*
        |--------------------------------------------------------------------------
        | Text Watermark
        |--------------------------------------------------------------------------
        */

        'text_enable' => 0,

        'text_content' => '',

        'text_size' => 22,

        'text_opacity' => 90,

        'text_position' => 'top-right',

        'text_color' => '#FFFFFF',

        /*
        |--------------------------------------------------------------------------
        | SEO
        |--------------------------------------------------------------------------
        */

        'seo_enable' => 1,

        'remove_metadata' => 1,

        'gaussian_noise' => 1,

        'noise_strength' => 6,

        'brightness_jitter' => 3,

        'contrast_jitter' => 3,

        'color_jitter' => 2,

        /*
        |--------------------------------------------------------------------------
        | Batch
        |--------------------------------------------------------------------------
        */

        'background_processing' => 1,

        'bulk_batch_size' => 20,
		
		'background_processing' => 1,

		'dry_run' => 0,

		'bulk_limit' => 0,

		'include_webp' => 0,

        /*
        |--------------------------------------------------------------------------
        | Log
        |--------------------------------------------------------------------------
        */

        'logging' => 1,

        'keep_logs_days' => 30,

    ];
}

/*
|--------------------------------------------------------------------------
| Sanitize Settings
|--------------------------------------------------------------------------
*/

function yio_sanitize_settings($input)
{
    $defaults = yio_default_settings();

    $output = [];

    foreach ($defaults as $key => $default) {

        if (!isset($input[$key])) {

            $output[$key] = $default;

            continue;
        }

        switch ($key) {

            case 'image_quality':
            case 'watermark_scale':
            case 'watermark_opacity':
            case 'watermark_padding':
            case 'text_size':
            case 'text_opacity':
            case 'noise_strength':
            case 'brightness_jitter':
            case 'contrast_jitter':
            case 'color_jitter':
            case 'max_width':
            case 'bulk_batch_size':
            case 'keep_logs_days':

                $output[$key] = absint($input[$key]);

                break;

            case 'watermark_image':
            case 'text_content':
            case 'text_color':
            case 'output_format':
            case 'watermark_position':
            case 'text_position':

                $output[$key] = sanitize_text_field($input[$key]);

                break;

            default:

                $output[$key] = (int) !empty($input[$key]);

        }

    }

    return $output;
}