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

    <h2>SEO Image Optimization</h2>

    <table class="form-table">


        <tr>

            <th scope="row">
                Enable SEO Optimization
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

                Enable image SEO optimization

            </td>

        </tr>



        <tr>

            <th scope="row">
                Remove Metadata
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

                Remove EXIF / IPTC / XMP metadata

            </td>

        </tr>



        <tr>

            <th scope="row">
                Auto Orientation
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

                Automatically fix mobile camera rotation

            </td>

        </tr>


    </table>



    <hr>


    <h2>Image Uniqueness Optimization</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Gaussian Noise
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

                Apply small pixel noise variation


            </td>


        </tr>



        <tr>

            <th scope="row">
                Noise Strength
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
                    Current default: 0.6%
                </p>

            </td>


        </tr>



        <tr>

            <th scope="row">
                Brightness Variation
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

                %

            </td>


        </tr>




        <tr>

            <th scope="row">
                Contrast Variation
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

                %

            </td>


        </tr>




        <tr>

            <th scope="row">
                Color Variation
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

                %

            </td>


        </tr>


    </table>




    <hr>



    <h2>Smart Resize</h2>



    <table class="form-table">


        <tr>

            <th scope="row">
                Enable Smart Resize
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

                Automatically resize large images


            </td>


        </tr>



        <tr>

            <th scope="row">
                Maximum Width
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

                px


                <p class="description">

                    Images smaller than this value will not be resized.

                </p>


            </td>


        </tr>



    </table>


    <?php

}
