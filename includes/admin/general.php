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
                <?php echo esc_html__('Enable Optimizer', 'yakuza-image-optimizer'); ?>
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

                    <?php echo esc_html__('Enable automatic image optimization', 'yakuza-image-optimizer'); ?>

                </label>

            </td>

        </tr>


        <tr>

            <th scope="row">
                <?php echo esc_html__('Process New Uploads', 'yakuza-image-optimizer'); ?>
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

                    <?php echo esc_html__('Automatically optimize newly uploaded images', 'yakuza-image-optimizer'); ?>

                </label>

            </td>

        </tr>


        <tr>

            <th scope="row">
                <?php echo esc_html__('Backup Original Image', 'yakuza-image-optimizer'); ?>
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

                    <?php echo esc_html__('Save original image before optimization', 'yakuza-image-optimizer'); ?>

                </label>

            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Output Format', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <select name="yio_settings[output_format]">


                    <option value="webp"
                    <?php selected(
                        $settings['output_format'] ?? '',
                        'webp'
                    ); ?>
                    >
                        <?php echo esc_html__('WebP', 'yakuza-image-optimizer'); ?>
                    </option>



                    <option value="avif"
                    <?php selected(
                        $settings['output_format'] ?? '',
                        'avif'
                    ); ?>
                    >
                        <?php echo esc_html__('AVIF (if supported)', 'yakuza-image-optimizer'); ?>
                    </option>


                </select>


                <p class="description">

                    <?php echo esc_html__('AVIF will automatically fallback to WebP if server does not support it.', 'yakuza-image-optimizer'); ?>

                </p>


            </td>


        </tr>


        <tr>

            <th scope="row">
                <?php echo esc_html__('Image Quality', 'yakuza-image-optimizer'); ?>
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
                    <?php echo esc_html__('Recommended: 75-85', 'yakuza-image-optimizer'); ?>
                </p>

            </td>

        </tr>


    </table>


    <?php

}
