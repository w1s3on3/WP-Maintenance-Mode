<?php
/*
Plugin Name: Maintenance Mode
Plugin URI: https://github.com/w1s3on3/WP-Maintenance-Mode
Description: A simple maintenance mode plugin for WordPress.
Version: 1.0
Author: Paul Wyers
Author URI: https://github.com/w1s3on3
License: GPL-2.0-or-later
*/

function my_maintenance_mode_settings_menu() {
    add_options_page(
        'Maintenance Mode Settings',
        'Maintenance Mode',
        'manage_options',
        'my-maintenance-mode',
        'my_maintenance_mode_settings_page'
    );
}
add_action('admin_menu', 'my_maintenance_mode_settings_menu');

// Register the background image setting
function my_maintenance_mode_register_settings() {
    register_setting('my-maintenance-mode', 'maintenance_enabled');
    register_setting('my-maintenance-mode', 'retry_after');
}
add_action('admin_init', 'my_maintenance_mode_register_settings');

// Create the settings page HTML
function my_maintenance_mode_settings_page() {
    ?>
    <div class="wrap">
        <h1>Maintenance Mode Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('my-maintenance-mode');
            do_settings_sections('my-maintenance-mode');
            $maintenance_enabled = get_option('maintenance_enabled', '');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable Maintenance Mode</th>
                    <td>
                        <input type="checkbox" name="maintenance_enabled" value="1" <?php checked(1, $maintenance_enabled); ?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Retry-After (seconds)</th>
                    <td>
                        <input type="number" name="retry_after" value="<?php echo esc_attr(get_option('retry_after', '3600')); ?>" class="regular-text" />
                        <p class="description">Set the Retry-After header value in seconds (e.g., 3600 for 1 hour). This informs search engines and browsers when to check back.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Maintenance mode function
function wp_maintenance_mode() {
    $maintenance_enabled = get_option('maintenance_enabled', '');

    if ($maintenance_enabled == '1' && !current_user_can('edit_themes') && !current_user_can('activate_plugins') && !is_user_logged_in()) {

        $retry_after = get_option('retry_after', '3600');
        header("Retry-After: {$retry_after}");

        $title = get_bloginfo('name') . ' - Under Maintenance';

        $maintenance_message = '
            <div class="maintenance-mode">
                <div class="content">
                    <div class="logo-container">
                        '. get_custom_logo() .'
                    </div>
                    <h1 class="header-text">Our site is coming soon</h1>
                    <p class="secondary-text">
                        We are doing some maintenance on our site. It won\'t take long, we promise. Come back and visit us again in a few days. Thank you for your patience!<br>
                        <br>
                        '. get_bloginfo('description') .'
                    </p>
                </div>
            </div>';

        wp_die($maintenance_message, 'Website Under Maintenance', ['response' => 503]);
    }
}
add_action('get_header', 'wp_maintenance_mode');

function my_maintenance_mode_enqueue_scripts($hook) {
    if ('settings_page_my-maintenance-mode' !== $hook) {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'my_maintenance_mode_enqueue_scripts');
