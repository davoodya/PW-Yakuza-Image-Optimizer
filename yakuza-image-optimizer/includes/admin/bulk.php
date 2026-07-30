<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Bulk Optimizer Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_bulk()
{

    $settings = yio_get_options();

    ?>

    <h2>Bulk Image Optimization</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Background Processing
            </th>


            <td>

                <input
                    type="checkbox"
                    name="yio_settings[background_processing]"
                    value="1"

                    <?php checked(
                        $settings['background_processing'] ?? 0,
                        1
                    ); ?>

                >

                Enable background processing


                <p class="description">

                    Images will be processed in small batches to reduce server load.

                </p>


            </td>


        </tr>



        <tr>

            <th scope="row">
                Batch Size
            </th>


            <td>


                <input
                    type="number"
                    min="1"
                    max="100"
                    name="yio_settings[bulk_batch_size]"
                    value="<?php echo esc_attr(
                        $settings['bulk_batch_size'] ?? 20
                    ); ?>"
                >


                images per batch


                <p class="description">

                    Recommended: 10-30 for shared hosting.

                </p>


            </td>


        </tr>



    </table>



    <hr>



    <h2>Optimization Mode</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Dry Run
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[dry_run]"
                    value="1"

                    <?php checked(
                        $settings['dry_run'] ?? 0,
                        1
                    ); ?>

                >


                Only scan and report files without changing them.


                <p class="description">

                    Recommended before first bulk optimization.

                </p>


            </td>


        </tr>



        <tr>

            <th scope="row">
                Processing Limit
            </th>


            <td>


                <input
                    type="number"
                    min="0"
                    max="100000"
                    name="yio_settings[bulk_limit]"
                    value="<?php echo esc_attr(
                        $settings['bulk_limit'] ?? 0
                    ); ?>"
                >


                <p class="description">

                    0 = Process all images.

                </p>


            </td>


        </tr>


    </table>



    <hr>



    <h2>Regenerate Options</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Include Existing WebP
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[include_webp]"
                    value="1"

                    <?php checked(
                        $settings['include_webp'] ?? 0,
                        1
                    ); ?>

                >


                Re-optimize existing WebP files.


            </td>


        </tr>

    </table>

    <?php

}