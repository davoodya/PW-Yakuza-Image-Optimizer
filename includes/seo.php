<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| SEO Image Processing
|--------------------------------------------------------------------------
*/

/**
 * Apply SEO optimization to an Imagick instance.
 *
 * Called by the core pipeline. The engine self-gates on its own settings
 * (seo_enable) and returns the image untouched when disabled.
 *
 * @param Imagick $imagick      The working image.
 * @param int     $attachment_id Attachment ID (unused in Stage 1).
 * @return Imagick
 */
function yio_apply_seo_optimization($imagick, $attachment_id = 0)
{
    /*
        Future features (Stage 3):

        - Remove metadata
        - Auto orientation (handled by the pipeline)
        - Smart resize (handled by the pipeline)
        - Brightness jitter
        - Contrast jitter
        - Color jitter
        - Gaussian noise
    */

    return $imagick;
}
