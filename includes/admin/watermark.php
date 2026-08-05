<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Watermark Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_watermark()
{

    $settings = yio_get_options();

    ?>

    <h2><?php echo esc_html__('Image Watermark', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Enable Image Watermark', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <label>

                    <input
                        type="checkbox"
                        name="yio_settings[watermark_enable]"
                        value="1"
                        <?php checked(
                            $settings['watermark_enable'] ?? 0,
                            1
                        ); ?>
                    >

                    <?php echo esc_html__('Enable image watermark', 'yakuza-image-optimizer'); ?>

                </label>

            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Watermark Image ID', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    name="yio_settings[watermark_image]"
                    value="<?php echo esc_attr(
                        $settings['watermark_image'] ?? ''
                    ); ?>"
                >


                <p class="description">
                    <?php echo esc_html__('Enter WordPress Media Attachment ID.', 'yakuza-image-optimizer'); ?>
                </p>


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Watermark Scale (%)', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="5"
                    max="100"
                    name="yio_settings[watermark_scale]"
                    value="<?php echo esc_attr(
                        $settings['watermark_scale'] ?? 33
                    ); ?>"
                >

            </td>

        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Watermark Opacity', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="1"
                    max="100"
                    name="yio_settings[watermark_opacity]"
                    value="<?php echo esc_attr(
                        $settings['watermark_opacity'] ?? 60
                    ); ?>"
                >

            </td>

        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Position', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <select name="yio_settings[watermark_position]">


                    <?php

                    $positions = [

                        'top-left'      => __('Top Left', 'yakuza-image-optimizer'),
                        'top-center'    => __('Top Center', 'yakuza-image-optimizer'),
                        'top-right'     => __('Top Right', 'yakuza-image-optimizer'),

                        'center-left'   => __('Center Left', 'yakuza-image-optimizer'),
                        'center'        => __('Center', 'yakuza-image-optimizer'),
                        'center-right'  => __('Center Right', 'yakuza-image-optimizer'),

                        'bottom-left'   => __('Bottom Left', 'yakuza-image-optimizer'),
                        'bottom-center' => __('Bottom Center', 'yakuza-image-optimizer'),
                        'bottom-right'  => __('Bottom Right', 'yakuza-image-optimizer'),

                    ];


                    foreach ($positions as $key => $label):

                    ?>


                    <option
                        value="<?php echo esc_attr($key); ?>"
                        <?php selected(
                            $settings['watermark_position'] ?? '',
                            $key
                        ); ?>
                    >

                        <?php echo esc_html($label); ?>

                    </option>


                    <?php endforeach; ?>


                </select>


            </td>

        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Padding', 'yakuza-image-optimizer'); ?>
            </th>


            <td>

                <input
                    type="number"
                    min="0"
                    max="200"
                    name="yio_settings[watermark_padding]"
                    value="<?php echo esc_attr(
                        $settings['watermark_padding'] ?? 5
                    ); ?>"
                >

            </td>

        </tr>


    </table>



    <hr>


    <h2><?php echo esc_html__('Text Watermark', 'yakuza-image-optimizer'); ?></h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                <?php echo esc_html__('Enable Text Watermark', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[text_enable]"
                    value="1"
                    <?php checked(
                        $settings['text_enable'] ?? 0,
                        1
                    ); ?>
                >


                <?php echo esc_html__('Enable text watermark', 'yakuza-image-optimizer'); ?>


            </td>

        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Text', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="text"
                    class="regular-text"
                    name="yio_settings[text_content]"
                    value="<?php echo esc_attr(
                        $settings['text_content'] ?? ''
                    ); ?>"
                >


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Font Size', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="8"
                    max="200"
                    name="yio_settings[text_size]"
                    value="<?php echo esc_attr(
                        $settings['text_size'] ?? 22
                    ); ?>"
                >


            </td>


        </tr>



        <tr>

            <th scope="row">
                <?php echo esc_html__('Text Color', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="text"
                    name="yio_settings[text_color]"
                    value="<?php echo esc_attr(
                        $settings['text_color'] ?? '#FFFFFF'
                    ); ?>"
                >


            </td>


        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Text Opacity', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <input
                    type="number"
                    min="1"
                    max="100"
                    name="yio_settings[text_opacity]"
                    value="<?php echo esc_attr(
                        $settings['text_opacity'] ?? 90
                    ); ?>"
                >


            </td>


        </tr>




        <tr>

            <th scope="row">
                <?php echo esc_html__('Text Position', 'yakuza-image-optimizer'); ?>
            </th>


            <td>


                <select name="yio_settings[text_position]">


                <?php foreach ($positions as $key => $label): ?>


                    <option
                        value="<?php echo esc_attr($key); ?>"
                        <?php selected(
                            $settings['text_position'] ?? '',
                            $key
                        ); ?>
                    >

                        <?php echo esc_html($label); ?>


                    </option>


                <?php endforeach; ?>


                </select>


            </td>


        </tr>



    </table>


    <?php

}
