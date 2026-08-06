<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Apply Watermark
|--------------------------------------------------------------------------
*/

/**
 * Apply image/text watermark to an Imagick instance.
 *
 * Called by the core pipeline. Self-gating: when both watermark_enable
 * and text_enable are disabled the image passes through untouched.
 *
 * @param Imagick $imagick       The working image.
 * @param int     $attachment_id The attachment being processed.
 * @return Imagick
 */
function yio_apply_watermark($imagick, $attachment_id = 0)
{
    if (
        !yio_get_option('watermark_enable', 1)
        &&
        !yio_get_option('text_enable', 0)
    ) {
        return $imagick;
    }

    try {

        $imagick = yio_apply_image_watermark($imagick, $attachment_id);

        $imagick = yio_apply_text_watermark($imagick, $attachment_id);

    } catch (Throwable $e) {

        yio_log('Watermark error (' . $e->getMessage() . ')');

    }

    return $imagick;
}

/*
|--------------------------------------------------------------------------
| Image Watermark
|--------------------------------------------------------------------------
*/

/**
 * Composite a PNG watermark attachment onto the image.
 *
 * @param Imagick $imagick
 * @param int     $attachment_id
 * @return Imagick
 */
function yio_apply_image_watermark($imagick, $attachment_id)
{
    if (!yio_get_option('watermark_enable', 1)) {
        return $imagick;
    }

    $watermark_id = (int) yio_get_option('watermark_image', 0);

    if ($watermark_id <= 0) {
        return $imagick;
    }

    // Never use the image being processed as its own watermark.
    if ($watermark_id === (int) $attachment_id) {
        return $imagick;
    }

    $watermark_file = get_attached_file($watermark_id);

    if (!$watermark_file || !file_exists($watermark_file)) {
        yio_log('Watermark image not found (attachment ' . $watermark_id . ')');
        return $imagick;
    }

    $stamp = new Imagick($watermark_file);

    $geometry = $imagick->getImageGeometry();
    $img_w    = (int) $geometry['width'];
    $img_h    = (int) $geometry['height'];

    $scale   = max(1, min(100, (int) yio_get_option('watermark_scale', 33)));
    $padding = max(0, (int) yio_get_option('watermark_padding', 5));
    $opacity = max(1, min(100, (int) yio_get_option('watermark_opacity', 60))) / 100;

    // Scale the stamp width to a percentage of the image width.
    $target_w = max(1, (int) round($img_w * $scale / 100));

    $stamp_geometry = $stamp->getImageGeometry();
    $stamp_w        = (int) $stamp_geometry['width'];
    $stamp_h        = (int) $stamp_geometry['height'];

    if ($stamp_w > $target_w) {
        $stamp->resizeImage($target_w, 0, Imagick::FILTER_LANCZOS, 1);
    }

    $stamp_geometry = $stamp->getImageGeometry();
    $stamp_w        = (int) $stamp_geometry['width'];
    $stamp_h        = (int) $stamp_geometry['height'];

    // Never let the stamp exceed the image height either.
    if ($stamp_h > $img_h) {
        $stamp->resizeImage(0, $img_h, Imagick::FILTER_LANCZOS, 1);
    }

    $stamp_geometry = $stamp->getImageGeometry();
    $stamp_w        = (int) $stamp_geometry['width'];
    $stamp_h        = (int) $stamp_geometry['height'];

    // Fade the stamp by multiplying its alpha channel, preserving its shape.
    $stamp->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
    $stamp->evaluateImage(Imagick::CHANNEL_ALPHA, Imagick::EVALUATE_MULTIPLY, $opacity);

    list($x, $y) = yio_watermark_position(
        $stamp_w,
        $stamp_h,
        $img_w,
        $img_h,
        yio_get_option('watermark_position', 'bottom-left'),
        $padding
    );

    $imagick->compositeImage($stamp, Imagick::COMPOSITE_OVER, $x, $y);

    $stamp->clear();
    $stamp->destroy();

    yio_log('Image watermark applied (attachment ' . $watermark_id . ')');

    return $imagick;
}

/*
|--------------------------------------------------------------------------
| Text Watermark
|--------------------------------------------------------------------------
*/

/**
 * Draw a text watermark onto the image.
 *
 * The text is rendered on a transparent canvas, trimmed to its bounding
 * box, faded, and then composited like a normal stamp. Note: glyph
 * rendering depends on fonts available on the server; non-Latin scripts
 * (e.g. Persian) require a server font that covers them.
 *
 * @param Imagick $imagick
 * @param int     $attachment_id
 * @return Imagick
 */
function yio_apply_text_watermark($imagick, $attachment_id)
{
    if (!yio_get_option('text_enable', 0)) {
        return $imagick;
    }

    $text = trim((string) yio_get_option('text_content', ''));

    if ($text === '') {
        return $imagick;
    }

    $geometry = $imagick->getImageGeometry();
    $img_w    = (int) $geometry['width'];
    $img_h    = (int) $geometry['height'];

    $font_size = max(8, (int) yio_get_option('text_size', 22));
    $padding   = max(0, (int) yio_get_option('watermark_padding', 5));
    $opacity   = max(1, min(100, (int) yio_get_option('text_opacity', 90))) / 100;
    $position  = yio_get_option('text_position', 'top-right');

    $color = (string) yio_get_option('text_color', '#FFFFFF');

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#FFFFFF';
    }

    // Render the text on a transparent canvas, then trim it down to its
    // bounding box so it behaves like a stamp. The canvas is sized from a
    // generous estimate (1.2em per byte, never larger than the image)
    // instead of the full image, avoiding a transient multi-MB allocation
    // on very large sources when smart-resize is disabled.
    $est_width  = (int) (strlen($text) * $font_size * 1.2) + ($font_size * 2);
    $est_height = $font_size * 3;

    $canvas_w = max(1, min($img_w, $est_width));
    $canvas_h = max(1, min($img_h, $est_height));

    $stamp = new Imagick();

    $stamp->newImage($canvas_w, $canvas_h, 'transparent');
    $stamp->setImageFormat('png');

    $draw = new ImagickDraw();

    $draw->setFillColor($color);
    $draw->setFontSize($font_size);
    $draw->setGravity(Imagick::GRAVITY_NORTHWEST);

    $stamp->annotateImage($draw, 0, $font_size, 0, $text);

    $stamp->trimImage(0);

    $stamp_geometry = $stamp->getImageGeometry();
    $stamp_w        = (int) $stamp_geometry['width'];
    $stamp_h        = (int) $stamp_geometry['height'];

    if ($stamp_w < 1 || $stamp_h < 1) {
        $stamp->clear();
        $stamp->destroy();
        return $imagick;
    }

    // Fade the text by multiplying its alpha channel.
    $stamp->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
    $stamp->evaluateImage(Imagick::CHANNEL_ALPHA, Imagick::EVALUATE_MULTIPLY, $opacity);

    list($x, $y) = yio_watermark_position(
        $stamp_w,
        $stamp_h,
        $img_w,
        $img_h,
        $position,
        $padding
    );

    $imagick->compositeImage($stamp, Imagick::COMPOSITE_OVER, $x, $y);

    $stamp->clear();
    $stamp->destroy();

    yio_log('Text watermark applied: "' . $text . '"');

    return $imagick;
}

/*
|--------------------------------------------------------------------------
| Watermark Existing Attachment
|--------------------------------------------------------------------------
*/

/**
 * Apply the configured watermark(s) to an existing attachment, keeping its
 * current file format (no conversion).
 *
 * Used by the "Apply to All" run and the per-image Media Library actions.
 * Never runs automatically — existing images are only watermarked when an
 * admin explicitly triggers this.
 *
 * @param int $attachment_id
 * @return array {bucket: watermarked|skipped|failed, message}
 */
function yio_apply_watermark_existing($attachment_id)
{
    $attachment_id = (int) $attachment_id;

    if (
        !yio_get_option('watermark_enable', 1)
        &&
        !yio_get_option('text_enable', 0)
    ) {
        return array('bucket' => 'skipped', 'message' => sprintf(
            __('#%1$d: watermark disabled', 'yakuza-image-optimizer'),
            $attachment_id
        ));
    }

    $file = get_attached_file($attachment_id);

    if (!$file || !file_exists($file)) {
        return array('bucket' => 'failed', 'message' => sprintf(
            __('#%1$d: file missing on disk', 'yakuza-image-optimizer'),
            $attachment_id
        ));
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($ext === 'gif' && yio_is_animated_gif($file)) {
        return array('bucket' => 'skipped', 'message' => sprintf(
            __('#%1$d: animated GIF skipped', 'yakuza-image-optimizer'),
            $attachment_id
        ));
    }

    try {

        $imagick = new Imagick($file);

        if ($imagick->getNumberImages() > 1) {
            $imagick->setIteratorIndex(0);
        }

        // Back up the current (pre-watermark) file so the restore system
        // can bring it back. Idempotent: skips when a backup already
        // exists for this path.
        if (yio_get_option('backup_original', 1)) {
            yio_create_backup($file);
        }

        $imagick = yio_apply_watermark($imagick, $attachment_id);

        // Keep the file's current format; just write the watermarked
        // pixels back over it.
        $quality = (int) yio_get_option('image_quality', 80);

        $imagick->setImageCompressionQuality($quality);
        $imagick->setCompressionQuality($quality);

        try {
            $written = $imagick->writeImage($file);
        } finally {
            $imagick->clear();
            $imagick->destroy();
        }

        if (!$written) {
            return array('bucket' => 'failed', 'message' => sprintf(
                __('#%1$d: could not write watermarked file', 'yakuza-image-optimizer'),
                $attachment_id
            ));
        }

        // Regenerate the size variants from the watermarked file. Pause
        // the pipeline so the metadata filter does not re-optimize it.
        yio_restore_active(true);

        try {

            $metadata = wp_generate_attachment_metadata($attachment_id, $file);

            if (is_array($metadata)) {
                wp_update_attachment_metadata($attachment_id, $metadata);
            }

        } finally {

            yio_restore_active(false);
        }

        yio_log('Watermark applied to existing attachment #' . $attachment_id . ': ' . $file);

        return array('bucket' => 'watermarked', 'message' => sprintf(
            __('#%1$d: watermarked %2$s', 'yakuza-image-optimizer'),
            $attachment_id,
            basename($file)
        ));

    } catch (Throwable $e) {

        yio_log('Watermark apply error on #' . $attachment_id . ': ' . $e->getMessage());

        return array('bucket' => 'failed', 'message' => sprintf(
            __('#%1$d: error — %2$s', 'yakuza-image-optimizer'),
            $attachment_id,
            $e->getMessage()
        ));
    }
}

/*
|--------------------------------------------------------------------------
| Watermark Position Helper
|--------------------------------------------------------------------------
*/

/**
 * Compute the stamp x/y for one of the 9 named positions.
 *
 * @param int    $stamp_w
 * @param int    $stamp_h
 * @param int    $img_w
 * @param int    $img_h
 * @param string $position
 * @param int    $padding
 * @return array [x, y]
 */
function yio_watermark_position($stamp_w, $stamp_h, $img_w, $img_h, $position, $padding)
{
    $position = (string) $position;

    // Center by default.
    $x = (int) (($img_w - $stamp_w) / 2);
    $y = (int) (($img_h - $stamp_h) / 2);

    switch ($position) {

        case 'top-left':
            $x = $padding;
            $y = $padding;
            break;

        case 'top-center':
            $y = $padding;
            break;

        case 'top-right':
            $x = $img_w - $stamp_w - $padding;
            $y = $padding;
            break;

        case 'center-left':
            $x = $padding;
            break;

        case 'center-right':
            $x = $img_w - $stamp_w - $padding;
            break;

        case 'bottom-left':
            $x = $padding;
            $y = $img_h - $stamp_h - $padding;
            break;

        case 'bottom-center':
            $y = $img_h - $stamp_h - $padding;
            break;

        case 'bottom-right':
            $x = $img_w - $stamp_w - $padding;
            $y = $img_h - $stamp_h - $padding;
            break;
    }

    // Keep the stamp fully inside the image.
    $x = max(0, min((int) $x, $img_w - $stamp_w));
    $y = max(0, min((int) $y, $img_h - $stamp_h));

    return array((int) $x, (int) $y);
}
