<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Apply Image Watermark
|--------------------------------------------------------------------------
*/

/**
 * Apply image/text watermark to an Imagick instance.
 *
 * Called by the core pipeline. The engine self-gates on its own settings
 * (watermark_enable / text_enable) and returns the image untouched when
 * both are disabled.
 *
 * @param Imagick $imagick      The working image.
 * @param int     $attachment_id Attachment ID (unused in Stage 1).
 * @return Imagick
 */
function yio_apply_watermark($imagick, $attachment_id = 0)
{
    /*
        Watermark engine will be implemented in Stage 2.

        Features:
        - PNG watermark
        - Scale
        - Position
        - Opacity
        - Text watermark
    */

    return $imagick;
}
