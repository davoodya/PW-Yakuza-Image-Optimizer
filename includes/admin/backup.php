<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Backup Settings Tab
|--------------------------------------------------------------------------
*/

function yio_tab_backup()
{

    $settings = yio_get_options();

    ?>

    <h2>Original Image Backup</h2>


    <table class="form-table">


        <tr>

            <th scope="row">
                Backup Original Images
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[backup_original]"
                    value="1"

                    <?php checked(
                        $settings['backup_original'] ?? 0,
                        1
                    ); ?>

                >


                Keep original images before optimization


                <p class="description">

                    Original files will be stored in:
                    <br>

                    wp-content/uploads/original-img/

                </p>


            </td>


        </tr>



        <tr>

            <th scope="row">
                Backup Structure
            </th>


            <td>


                <code>
                original-img/YYYY/MM/
                </code>


                <p class="description">

                    Folder structure will match WordPress uploads.

                </p>


            </td>


        </tr>



    </table>



    <hr>



    <h2>Restore Options</h2>



    <table class="form-table">


        <tr>

            <th scope="row">
                Restore Method
            </th>


            <td>


                <select
                    name="yio_settings[restore_method]"
                >


                    <option
                        value="replace"
                        <?php selected(
                            $settings['restore_method'] ?? '',
                            'replace'
                        ); ?>
                    >

                        Replace optimized file

                    </option>



                    <option
                        value="copy"
                        <?php selected(
                            $settings['restore_method'] ?? '',
                            'copy'
                        ); ?>
                    >

                        Restore as new file

                    </option>


                </select>


            </td>


        </tr>



    </table>



    <hr>



    <h2>Image Comparison</h2>



    <table class="form-table">


        <tr>

            <th scope="row">
                Enable Comparison
            </th>


            <td>


                <input
                    type="checkbox"
                    name="yio_settings[image_comparison]"
                    value="1"

                    <?php checked(
                        $settings['image_comparison'] ?? 0,
                        1
                    ); ?>

                >


                Enable before / after comparison


            </td>


        </tr>


    </table>


    <?php

}