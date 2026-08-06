<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Media Library Watermark Actions
|--------------------------------------------------------------------------
| Adds a per-image "Apply Watermark" row action and a bulk action to the
| Media Library list, so existing images can be watermarked individually.
*/

function yio_media_actions_init()
{
    add_filter('media_row_actions', 'yio_media_row_actions', 10, 2);
    add_filter('bulk_actions-upload', 'yio_media_bulk_actions');
    add_filter('handle_bulk_actions-upload', 'yio_media_handle_bulk_action', 10, 3);
    add_action('admin_post_yio_apply_watermark', 'yio_media_handle_single_action');
    add_action('admin_notices', 'yio_media_action_notices');
}

/*
|--------------------------------------------------------------------------
| Row Action
|--------------------------------------------------------------------------
*/

function yio_media_row_actions($actions, $post)
{
    if (
        !current_user_can('upload_files')
        || empty($post->post_mime_type)
        || strpos($post->post_mime_type, 'image/') !== 0
    ) {
        return $actions;
    }

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=yio_apply_watermark&attachment_id=' . (int) $post->ID),
        'yio_apply_watermark_' . (int) $post->ID
    );

    $actions['yio_apply_watermark'] = sprintf(
        '<a href="%s">%s</a>',
        esc_url($url),
        esc_html__('Apply Watermark', 'yakuza-image-optimizer')
    );

    return $actions;
}

/*
|--------------------------------------------------------------------------
| Bulk Action
|--------------------------------------------------------------------------
*/

function yio_media_bulk_actions($actions)
{
    $actions['yio_apply_watermark'] = __('Apply Watermark', 'yakuza-image-optimizer');
    return $actions;
}

function yio_media_handle_bulk_action($redirect, $doaction, $object_ids)
{
    if ($doaction !== 'yio_apply_watermark') {
        return $redirect;
    }

    if (!current_user_can('upload_files')) {
        return $redirect;
    }

    $count = 0;

    foreach ((array) $object_ids as $attachment_id) {

        $result = yio_apply_watermark_existing((int) $attachment_id);

        if ($result['bucket'] === 'watermarked') {
            $count++;
        }
    }

    return add_query_arg(
        array('yio_watermarked' => $count, 'yio_wm_run' => 1),
        $redirect
    );
}

/*
|--------------------------------------------------------------------------
| Single (Row) Action Handler
|--------------------------------------------------------------------------
*/

function yio_media_handle_single_action()
{
    $attachment_id = isset($_GET['attachment_id']) ? (int) $_GET['attachment_id'] : 0;

    if (!current_user_can('upload_files') || !$attachment_id) {
        wp_safe_redirect(add_query_arg(
            array('yio_wm_error' => 1, 'yio_wm_run' => 1),
            admin_url('upload.php')
        ));
        exit;
    }

    check_admin_referer('yio_apply_watermark_' . $attachment_id);

    $result = yio_apply_watermark_existing($attachment_id);

    wp_safe_redirect(add_query_arg(
        array(
            'yio_watermarked' => $result['bucket'] === 'watermarked' ? 1 : 0,
            'yio_wm_error'    => $result['bucket'] === 'failed' ? 1 : 0,
            'yio_wm_run'      => 1,
        ),
        admin_url('upload.php')
    ));

    exit;
}

/*
|--------------------------------------------------------------------------
| Result Notices
|--------------------------------------------------------------------------
*/

function yio_media_action_notices()
{
    if (empty($_GET['yio_wm_run'])) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen || $screen->id !== 'upload') {
        return;
    }

    $count = isset($_GET['yio_watermarked']) ? (int) $_GET['yio_watermarked'] : 0;
    $error = !empty($_GET['yio_wm_error']);

    if ($error) {

        echo '<div class="notice notice-error is-dismissible"><p>'
            . esc_html__('Could not apply the watermark to that image. Check the debug log for details.', 'yakuza-image-optimizer')
            . '</p></div>';

    } elseif ($count > 0) {

        echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html(sprintf(
                /* translators: %d: number of watermarked images. */
                _n('Watermark applied to %d image.', 'Watermark applied to %d images.', $count, 'yakuza-image-optimizer'),
                $count
            ))
            . '</p></div>';

    } else {

        echo '<div class="notice notice-info is-dismissible"><p>'
            . esc_html__('No images were watermarked.', 'yakuza-image-optimizer')
            . '</p></div>';
    }
}
