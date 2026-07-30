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

    <h2>Image Watermark</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Enable Image Watermark
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

                    Enable image watermark

                </label>

            </td>


        </tr>



        <tr>

            <th scope="row">
                Watermark Image ID
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
                    Enter WordPress Media Attachment ID.
                </p>


            </td>


        </tr>



        <tr>

            <th scope="row">
                Watermark Scale (%)
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
                Watermark Opacity
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
                Position
            </th>


            <td>


                <select name="yio_settings[watermark_position]">


                    <?php

                    $positions = [

                        'top-left'     => 'Top Left',
                        'top-center'   => 'Top Center',
                        'top-right'    => 'Top Right',

                        'center-left'  => 'Center Left',
                        'center'       => 'Center',
                        'center-right' => 'Center Right',

                        'bottom-left'  => 'Bottom Left',
                        'bottom-center'=> 'Bottom Center',
                        'bottom-right' => 'Bottom Right',

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
                Padding
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


    <h2>Text Watermark</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Enable Text Watermark
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


                Enable text watermark


            </td>

        </tr>



        <tr>

            <th scope="row">
                Text
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
                Font Size
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
                Text Color
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
                Text Opacity
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
                Text Position
            </th>


            <td>


                <select name="yio_settings[text_position]">


                <?php foreach ($positions as $key=>$label): ?>


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