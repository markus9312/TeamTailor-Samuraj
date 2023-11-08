<?php
/**
 * Plugin Name: CoSwift
 * Plugin URI: https://example.com/coswift
 * Description: TeamTailor Job Listings Sync for WordPress.
 * Version: 0.1
 * Author: dotMavriQ
 * Author URI: https://github.com/dotMavriQ
 * License: MIT
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// SVG icon for the menu
$bunny_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 3.87 3.13 7 7 7s7-3.13 7-7c0-3.87-3.13-9-7-9zm0 12c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm2-5c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2zM8.5 7C7.67 7 7 6.33 7 5.5S7.67 4 8.5 4 10 4.67 10 5.5 9.33 7 8.5 7zm7 0c-.83 0-1.5-.67-1.5-1.5S14.67 4 15.5 4 17 4.67 17 5.5 16.33 7 15.5 7z"/></svg>';
$bunny_icon_data_url = 'data:image/svg+xml;base64,' . base64_encode($bunny_icon_svg);

/**
 * Add a menu item for the plugin in the admin area.
 */
function coswift_add_admin_menu() {
    add_menu_page(
        'CoSwift Settings',        // Page title
        'CoSwift 🐰',              // Menu title with bunny emoji
        'manage_options',          // Capability
        'coswift',                 // Menu slug
        'coswift_settings_page',   // Function to display the settings page
        $bunny_icon_data_url       // Icon URL
    );
}

add_action('admin_menu', 'coswift_add_admin_menu');

function coswift_settings_page() {
    ?>
    <div class="wrap">
        <h2>CoSwift Settings</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque finibus massa a justo malesuada, a ultrices massa efficitur. Sed sed lacinia lorem. Nulla facilisi. Aenean ac eleifend tortor, ut varius arcu.</p>
        <form action="options.php" method="POST">
            <?php
            settings_fields('coswift-settings');
            do_settings_sections('coswift');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="teamtailor_api_link"><strong>TeamTailor API link:</strong></label></th>
                    <td>
                        <input type="text" id="teamtailor_api_link" name="coswift_teamtailor_api_link" value="<?php echo esc_attr(get_option('coswift_teamtailor_api_link')); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
            <?php submit_button('Sync'); ?>
        </form>
        <div>
            <img src="<?php echo plugins_url('CoSwift_Logo.png', __FILE__); ?>" alt="CoSwift Logo">
        </div>
    </div>
    <?php
}

function coswift_register_settings() {
    register_setting('coswift-settings', 'coswift_teamtailor_api_link');
}

add_action('admin_init', 'coswift_register_settings');
