<?php
if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| General Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_general()
{

    $settings = yio_get_options();

    ?>

    <table class="form-table">

        <tr>

            <th scope="row">
                Enable Optimizer
            </th>

            <td>

                <label>

                    <input 
                        type="checkbox"
                        name="yio_settings[enabled]"
                        value="1"
                        <?php checked(
                            $settings['enabled'] ?? 0,
                            1
                        ); ?>
                    >

                    Enable automatic image optimization

                </label>

            </td>

        </tr>


        <tr>

            <th scope="row">
                Process New Uploads
            </th>

            <td>

                <label>

                    <input 
                        type="checkbox"
                        name="yio_settings[process_new_uploads]"
                        value="1"
                        <?php checked(
                            $settings['process_new_uploads'] ?? 0,
                            1
                        ); ?>
                    >

                    Automatically optimize newly uploaded images

                </label>

            </td>

        </tr>


        <tr>

            <th scope="row">
                Backup Original Image
            </th>

            <td>

                <label>

                    <input 
                        type="checkbox"
                        name="yio_settings[backup_original]"
                        value="1"
                        <?php checked(
                            $settings['backup_original'] ?? 0,
                            1
                        ); ?>
                    >

                    Save original image before optimization

                </label>

            </td>

        </tr>



        <tr>

            <th scope="row">
                Output Format
            </th>


            <td>


                <select name="yio_settings[output_format]">


                    <option value="webp"
                    <?php selected(
                        $settings['output_format'] ?? '',
                        'webp'
                    ); ?>
                    >
                        WebP
                    </option>



                    <option value="avif"
                    <?php selected(
                        $settings['output_format'] ?? '',
                        'avif'
                    ); ?>
                    >
                        AVIF (if supported)
                    </option>


                </select>


                <p class="description">

                    AVIF will automatically fallback to WebP if server does not support it.

                </p>


            </td>


        </tr>


        <tr>

            <th scope="row">
                Image Quality
            </th>


            <td>

                <input
                    type="number"
                    min="1"
                    max="100"
                    name="yio_settings[image_quality]"
                    value="<?php echo esc_attr(
                        $settings['image_quality'] ?? 80
                    ); ?>"
                >

                <p class="description">
                    Recommended: 75-85
                </p>

            </td>

        </tr>


    </table>


    <?php

}