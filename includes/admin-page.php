<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Admin Menu
|--------------------------------------------------------------------------
*/

function yio_admin_menu()
{

    add_menu_page(
        __('Yakuza Image Optimizer', 'yakuza-image-optimizer'),
        __('YIO Optimizer', 'yakuza-image-optimizer'),
        'manage_options',
        'yakuza-image-optimizer',
        'yio_admin_page',
        'dashicons-format-image',
        58
    );

}


/*
|--------------------------------------------------------------------------
| Admin Page
|--------------------------------------------------------------------------
*/

function yio_admin_page()
{

    $tab = isset($_GET['tab'])
        ? sanitize_key($_GET['tab'])
        : 'general';



    $tabs = [

        'general'   => __('General', 'yakuza-image-optimizer'),
        'watermark' => __('Watermark', 'yakuza-image-optimizer'),
        'seo'       => __('SEO', 'yakuza-image-optimizer'),
        'backup'    => __('Backup', 'yakuza-image-optimizer'),
        'bulk'      => __('Bulk Optimize', 'yakuza-image-optimizer'),
        'logs'      => __('Logs', 'yakuza-image-optimizer'),
        'tools'     => __('Tools', 'yakuza-image-optimizer'),

    ];



    ?>


    <div class="wrap">


        <h1>
            <?php echo esc_html__('Yakuza Image Optimizer', 'yakuza-image-optimizer'); ?>
        </h1>


        <p>

            <?php echo esc_html__('Version:', 'yakuza-image-optimizer'); ?>
            <?php echo esc_html(YIO_VERSION); ?>

            <br>

            <?php echo esc_html__('Designed & Developed by', 'yakuza-image-optimizer'); ?>
            <strong>Davood Yahay</strong>

            <br>

            طراحی و توسعه توسط داوود یاحی

        </p>



        <hr>



        <h2 class="nav-tab-wrapper">


            <?php foreach ($tabs as $key => $title): ?>


                <a
                    href="?page=yakuza-image-optimizer&tab=<?php echo esc_attr($key); ?>"
                    class="nav-tab <?php echo ($tab === $key) ? 'nav-tab-active' : ''; ?>"
                >

                    <?php echo esc_html($title); ?>

                </a>


            <?php endforeach; ?>


        </h2>




        <form method="post" action="options.php">


            <?php

            settings_fields(
                'yio_settings_group'
            );


            switch ($tab) {


                case 'general':

                    yio_tab_general();

                    break;



                case 'watermark':

                    yio_tab_watermark();

                    break;



                case 'seo':

                    yio_tab_seo();

                    break;



                case 'backup':

                    yio_tab_backup();

                    break;



                case 'bulk':

                    yio_tab_bulk();

                    break;



                case 'logs':

                    yio_tab_logs();

                    break;



                case 'tools':

                    yio_tab_tools();

                    break;


            }


            submit_button();


            ?>


        </form>


    </div>


    <?php

}



/*
|--------------------------------------------------------------------------
| Temporary Tabs
|--------------------------------------------------------------------------
*/


function yio_tab_logs()
{

    echo '<h2>' . esc_html__('Logs', 'yakuza-image-optimizer') . '</h2>';

    echo '<p>' . esc_html__('Coming soon.', 'yakuza-image-optimizer') . '</p>';

}



function yio_tab_tools()
{

    echo '<h2>' . esc_html__('Tools', 'yakuza-image-optimizer') . '</h2>';

    echo '<p>' . esc_html__('Coming soon.', 'yakuza-image-optimizer') . '</p>';

}
