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
        'Yakuza Image Optimizer',
        'YIO Optimizer',
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

        'general'    => 'General',
        'watermark'  => 'Watermark',
        'seo'        => 'SEO',
        'backup'     => 'Backup',
        'bulk'       => 'Bulk Optimize',
        'logs'       => 'Logs',
        'tools'      => 'Tools',

    ];

    ?>

    <div class="wrap">

        <h1>Yakuza Image Optimizer</h1>

        <p>
            Version <?php echo esc_html(YIO_VERSION); ?>
            <br>
            Designed & Developed by
            <strong>Davood Yahay</strong>
            <br>
            طراحی و توسعه توسط داوود یاحی
        </p>

        <hr>

        <h2 class="nav-tab-wrapper">

            <?php foreach ($tabs as $key => $title) : ?>

                <a
                    href="?page=yakuza-image-optimizer&tab=<?php echo esc_attr($key); ?>"
                    class="nav-tab <?php echo ($tab == $key) ? 'nav-tab-active' : ''; ?>">

                    <?php echo esc_html($title); ?>

                </a>

            <?php endforeach; ?>

        </h2>

        <form method="post" action="options.php">

            <?php

            settings_fields('yio_settings_group');

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
| Tabs
|--------------------------------------------------------------------------
*/

function yio_tab_general()
{
    echo '<h2>General Settings</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}

function yio_tab_watermark()
{
    echo '<h2>Watermark Settings</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}

function yio_tab_seo()
{
    echo '<h2>SEO Optimization</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}

function yio_tab_backup()
{
    echo '<h2>Backup & Restore</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}

function yio_tab_bulk()
{
    echo '<h2>Bulk Optimizer</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}

function yio_tab_logs()
{
    echo '<h2>Logs</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}

function yio_tab_tools()
{
    echo '<h2>Tools</h2>';
    echo '<p>This section will be completed in the next step.</p>';
}