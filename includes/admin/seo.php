<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| SEO Optimization Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_seo()
{

    $settings = yio_get_options();

    ?>

    <h2><?php echo esc_html__('SEO Image Optimization', 'yakuza-image-optimizer'); ?></h2>

    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Enable SEO Optimization', 'yakuza-image-optimizer'); ?>
            </th>

            <td>

                <input
                    type="checkbox"
                    name="yio_settings[seo_enable]"
                    value="1"
                    <?php checked(
                        $settings['seo_enable'] ?? 0,
                        1
                    ); ?>
                >

                <?php echo esc_html__('Enable image SEO optimization', 'yakuza-image-optimizer'); ?>

            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Remove Metadata', 'yakuza-image-optimizer'); ?>
            </th>

            <td>

                <input
                    type="checkbox"
                    name="yio_settings[remove_metadata]"
                    value="1"
                    <?php checked(
                        $settings['remove_metadata'] ?? 0,
                        1
                    ); ?>
                >

                <?php echo esc_html__('Remove EXIF / IPTC / XMP metadata', 'yakuza-image-optimizer'); ?>

            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Auto Orientation', 'yakuza-image-optimizer'); ?>
            </th>

            <td>

                <input
                    type="checkbox"
                    name="yio_settings[auto_orientation]"
                    value="1"
                    <?php checked(
                        $settings['auto_orientation'] ?? 0,
                        1
                    ); ?>
                >

                <?php echo esc_html__('Automatically fix mobile camera rotation', 'yakuza-image-optimizer'); ?>

            </td>

        </tr>


    </table>



    <hr>


    <h2><?php echo esc_html__('Image Uniqueness Optimization', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Gaussian Noise', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[gaussian_noise]"
                    value="1"
                    <?php checked(
                        $settings['gaussian_noise'] ?? 0,
                        1
                    ); ?>
                >

                <?php echo esc_html__('Apply small pixel noise variation', 'yakuza-image-optimizer'); ?>


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Noise Strength', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="0"
                    max="20"
                    step="0.1"
                    name="yio_settings[noise_strength]"
                    value="<?php echo esc_attr(
                        $settings['noise_strength'] ?? 6
                    ); ?>"
                >

                <p class="description">
                    <?php echo esc_html__('Current default: 0.6%', 'yakuza-image-optimizer'); ?>
                </p>

            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Brightness Variation', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="0"
                    max="20"
                    name="yio_settings[brightness_jitter]"
                    value="<?php echo esc_attr(
                        $settings['brightness_jitter'] ?? 3
                    ); ?>"
                >

                <?php echo esc_html__('%', 'yakuza-image-optimizer'); ?>

            </td>


        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Contrast Variation', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="0"
                    max="20"
                    name="yio_settings[contrast_jitter]"
                    value="<?php echo esc_attr(
                        $settings['contrast_jitter'] ?? 3
                    ); ?>"
                >

                <?php echo esc_html__('%', 'yakuza-image-optimizer'); ?>

            </td>


        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Color Variation', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="0"
                    max="20"
                    name="yio_settings[color_jitter]"
                    value="<?php echo esc_attr(
                        $settings['color_jitter'] ?? 2
                    ); ?>"
                >

                <?php echo esc_html__('%', 'yakuza-image-optimizer'); ?>

            </td>


        </tr>


    </table>




    <hr>



    <h2><?php echo esc_html__('Smart Resize', 'yakuza-image-optimizer'); ?></h2>



    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Enable Smart Resize', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[smart_resize]"
                    value="1"
                    <?php checked(
                        $settings['smart_resize'] ?? 0,
                        1
                    ); ?>
                >

                <?php echo esc_html__('Automatically resize large images', 'yakuza-image-optimizer'); ?>


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Maximum Width', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="500"
                    max="5000"
                    name="yio_settings[max_width]"
                    value="<?php echo esc_attr(
                        $settings['max_width'] ?? 2000
                    ); ?>"
                >

                <?php echo esc_html__('px', 'yakuza-image-optimizer'); ?>


                <p class="description">

                    <?php echo esc_html__('Images smaller than this value will not be resized.', 'yakuza-image-optimizer'); ?>

                </p>


            </td>


        </tr>



    </table>


    <?php

}
