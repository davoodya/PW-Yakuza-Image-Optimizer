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
 * Called by the core pipeline. Self-gating: returns the image untouched
 * when seo_enable is off or no SEO feature is enabled.
 *
 * Strategy: strip identifying metadata (EXIF/IPTC/XMP), then apply a
 * deterministic, visually-imperceptible pixel variation (brightness,
 * contrast and color jitter seeded per attachment + site) plus faint
 * Gaussian noise, so every generated file is byte- and perceptually
 * unique for search engines — even when the same source image is used
 * on another site running the plugin.
 *
 * @param Imagick $imagick       The working image.
 * @param int     $attachment_id The attachment being processed.
 * @return Imagick
 */
function yio_apply_seo_optimization($imagick, $attachment_id = 0)
{
    if (!yio_get_option('seo_enable', 1)) {
        return $imagick;
    }

    $has_work = (
        yio_get_option('remove_metadata', 1)
        || yio_get_option('gaussian_noise', 1)
        || (int) yio_get_option('brightness_jitter', 3) > 0
        || (int) yio_get_option('contrast_jitter', 3) > 0
        || (int) yio_get_option('color_jitter', 2) > 0
    );

    if (!$has_work) {
        return $imagick;
    }

    $seed = yio_seo_seed($attachment_id);

    try {

        if (yio_get_option('remove_metadata', 1)) {
            yio_seo_strip_metadata($imagick);
        }

        yio_seo_apply_jitter($imagick, $seed);

        if (yio_get_option('gaussian_noise', 1)) {
            yio_seo_apply_noise($imagick);
        }

    } catch (Throwable $e) {

        yio_log('SEO optimization error (' . $e->getMessage() . ')');

    }

    return $imagick;
}

/*
|--------------------------------------------------------------------------
| Per-Image Seed
|--------------------------------------------------------------------------
*/

/**
 * Deterministic per-image seed: site key (wp_hash) + attached file path.
 * The same image on the same install always gets the same jitter, while
 * a copy of it on another install (different keys) gets different jitter.
 *
 * @param int $attachment_id
 * @return string
 */
function yio_seo_seed($attachment_id)
{
    $file = $attachment_id ? get_attached_file($attachment_id) : false;

    $key = $file ? $file : (string) $attachment_id;

    return (string) $attachment_id . '|' . wp_hash($key);
}

/*
|--------------------------------------------------------------------------
| Seeded Jitter
|--------------------------------------------------------------------------
*/

/**
 * A deterministic value in [-$max, $max] derived from the seed.
 *
 * @param string $seed
 * @param int    $index Axis discriminator (1, 2, 3, ...).
 * @param int    $max   Maximum absolute value.
 * @return int
 */
function yio_seeded_jitter($seed, $index, $max)
{
    $max = max(0, (int) $max);

    if ($max === 0) {
        return 0;
    }

    $byte = hexdec(substr(md5($seed . ':' . $index), 0, 2));

    $amount = (int) round(($byte % 101) / 100 * $max);
    $sign   = ($byte % 2 === 0) ? 1 : -1;

    return $sign * $amount;
}

/*
|--------------------------------------------------------------------------
| Jitter Engine
|--------------------------------------------------------------------------
*/

/**
 * Apply subtle brightness / contrast / color variation.
 *
 * @param Imagick $imagick
 * @param string  $seed
 * @return void
 */
function yio_seo_apply_jitter($imagick, $seed)
{
    $brightness_max = min(20, max(0, (int) yio_get_option('brightness_jitter', 3)));
    $contrast_max   = min(20, max(0, (int) yio_get_option('contrast_jitter', 3)));
    $color_max      = min(20, max(0, (int) yio_get_option('color_jitter', 2)));

    if ($brightness_max <= 0 && $contrast_max <= 0 && $color_max <= 0) {
        return;
    }

    $brightness = yio_seeded_jitter($seed, 1, $brightness_max);
    $saturation = yio_seeded_jitter($seed, 2, $color_max);
    $contrast   = yio_seeded_jitter($seed, 3, $contrast_max);

    // Brightness + color (saturation) in a single pass; 100 = no change.
    if ($brightness !== 0 || $saturation !== 0) {
        $imagick->modulateImage(100 + $brightness, 100 + $saturation, 100);
    }

    // Contrast on a -100..100 scale.
    if ($contrast !== 0) {

        if (method_exists($imagick, 'brightnessContrastImage')) {

            $imagick->brightnessContrastImage(0, $contrast);

        } else {

            yio_log('Contrast jitter skipped: brightnessContrastImage() unavailable.');
        }
    }
}

/*
|--------------------------------------------------------------------------
| Gaussian Noise
|--------------------------------------------------------------------------
*/

/**
 * Add faint Gaussian noise at the configured strength.
 *
 * Full-strength noise is generated on a clone, faded via alpha
 * multiplication (noise_strength is in tenths of a percent: 6 => 0.6%),
 * and blended over the image. The image's own alpha shape is preserved,
 * so transparent regions stay clean.
 *
 * @param Imagick $imagick
 * @return void
 */
function yio_seo_apply_noise($imagick)
{
    $strength = min(20, max(0, (float) yio_get_option('noise_strength', 6))) / 1000;

    if ($strength <= 0) {
        return;
    }

    $noise = clone $imagick;

    $noise->addNoiseImage(Imagick::NOISE_GAUSSIAN);

    $noise->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
    $noise->evaluateImage(Imagick::CHANNEL_ALPHA, Imagick::EVALUATE_MULTIPLY, $strength);

    $imagick->compositeImage($noise, Imagick::COMPOSITE_OVER, 0, 0);

    $noise->clear();
    $noise->destroy();
}

/*
|--------------------------------------------------------------------------
| Metadata Removal
|--------------------------------------------------------------------------
*/

/**
 * Strip identifying metadata (EXIF / IPTC / XMP) while keeping the ICC
 * color profile so colors do not shift.
 *
 * @param Imagick $imagick
 * @return void
 */
function yio_seo_strip_metadata($imagick)
{
    foreach (array('exif', 'iptc', 'xmp', '8bim', 'photoshop') as $profile) {

        if ($imagick->getImageProfile($profile)) {
            $imagick->removeImageProfile($profile);
        }
    }
}
