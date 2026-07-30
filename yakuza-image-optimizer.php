<?php
/**
 * Plugin Name: Yakuza Image Optimizer
 * Plugin URI: https://davoodya.ir
 * Description: Automatically optimize uploaded images, convert to WebP/AVIF, apply watermark, SEO optimization, backup originals and batch optimize existing images.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Davood Yahay
 * Author URI: https://davoodya.ir
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yakuza-image-optimizer
 * Domain Path: /languages
 *
 * طراحی و توسعه توسط داوود یاحی
 */

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
*/

define('YIO_VERSION', '1.0.0');
define('YIO_PLUGIN_FILE', __FILE__);
define('YIO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YIO_PLUGIN_URL', plugin_dir_url(__FILE__));

define('YIO_INC', YIO_PLUGIN_DIR . 'includes/');
define('YIO_ASSETS', YIO_PLUGIN_URL . 'assets/');

/*
|--------------------------------------------------------------------------
| Minimum Requirements
|--------------------------------------------------------------------------
*/

function yio_check_requirements()
{
    if (version_compare(PHP_VERSION, '8.1', '<')) {

        add_action('admin_notices', function () {

            echo '<div class="notice notice-error"><p><strong>Yakuza Image Optimizer</strong><br>';
            echo 'PHP 8.1 or newer is required.</p></div>';

        });

        return false;
    }

    if (!extension_loaded('imagick')) {

        add_action('admin_notices', function () {

            echo '<div class="notice notice-error"><p><strong>Yakuza Image Optimizer</strong><br>';
            echo 'Imagick PHP Extension is required.</p></div>';

        });

        return false;
    }

    return true;
}

if (!yio_check_requirements()) {
    return;
}

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
*/

require_once YIO_INC . 'helper.php';
require_once YIO_INC . 'settings.php';
require_once YIO_INC . 'admin-page.php';
require_once YIO_INC . 'logger.php';
require_once YIO_INC . 'backup.php';
require_once YIO_INC . 'restore.php';
require_once YIO_INC . 'watermark.php';
require_once YIO_INC . 'seo.php';
require_once YIO_INC . 'image-processor.php';
require_once YIO_INC . 'bulk.php';

/*
|--------------------------------------------------------------------------
| Activation
|--------------------------------------------------------------------------
*/

register_activation_hook(__FILE__, function () {

    if (!file_exists(WP_CONTENT_DIR . '/uploads/original-img')) {

        wp_mkdir_p(WP_CONTENT_DIR . '/uploads/original-img');

    }

});

/*
|--------------------------------------------------------------------------
| Plugin Init
|--------------------------------------------------------------------------
*/

add_action('plugins_loaded', function () {

    if (function_exists('yio_settings_init')) {
        yio_settings_init();
    }

    if (function_exists('yio_admin_menu')) {
        yio_admin_menu();
    }

    if (function_exists('yio_logger_init')) {
        yio_logger_init();
    }

    if (function_exists('yio_image_processor_init')) {
        yio_image_processor_init();
    }

    if (function_exists('yio_bulk_init')) {
        yio_bulk_init();
    }

});