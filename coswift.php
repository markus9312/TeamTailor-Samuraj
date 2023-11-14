<?php
/**
 * Plugin Name: CoSwift
 * Description: A WordPress plugin to fetch job listings from TeamTailor and create Custom Post Types.
 * Version: 0.1
 * Author: dotMavriQ
 */

// Check if accessed directly.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Admin menu hook.
add_action('admin_menu', 'coswift_admin_menu');

function coswift_admin_menu() {
    add_menu_page(
        'CoSwift Settings',
        'CoSwift',
        'manage_options',
        'coswift-settings',
        'coswift_settings_page'
    );
}

// Settings page content.
function coswift_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if POST request is made
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coswift_action']) && $_POST['coswift_action'] === 'save_settings') {
        // Check if token is set in POST request
        if (isset($_POST['coswift_api_token'])) {
            // Save the token using update_option
            update_option('coswift_api_token', sanitize_text_field($_POST['coswift_api_token']));
            echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Settings saved.</strong></p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h2>CoSwift Settings</h2>
        <form method="post">
            <?php
                settings_fields('coswift_options_group');
                do_settings_sections('coswift-settings');
                submit_button('Save Settings');
            ?>
            <input type="hidden" name="coswift_action" value="save_settings">
        </form>
        <form method="post">
            <input type="hidden" name="coswift_action" value="test_api">
            <?php submit_button('Test API'); ?>
        </form>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coswift_action']) && $_POST['coswift_action'] === 'test_api') {
                echo '<div class="json-output">';
                echo fetch_coswift_job_listings();
                echo '</div>';
            }
        ?>
    </div>
    <style>
        .json-output {
            max-height: 400px;
            max-width: 100%;
            overflow: auto;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
    <?php
}

// Register settings.
add_action('admin_init', 'coswift_register_settings');

function coswift_register_settings() {
    register_setting('coswift_options_group', 'coswift_api_token');
    add_settings_section('coswift_main_section', null, null, 'coswift-settings');
    add_settings_field('coswift_api_token', 'API Token', 'coswift_api_token_callback', 'coswift-settings', 'coswift_main_section');
}

function coswift_api_token_callback() {
    $token = get_option('coswift_api_token');
    echo '<input type="text" id="coswift_api_token" name="coswift_api_token" value="' . esc_attr($token) . '" style="width: 330px;" />';
}

// Function to fetch job listings.
function fetch_coswift_job_listings() {
    $token = get_option('coswift_api_token');

    if (!$token) {
        return 'API Token is not set.';
    }

    $url = "https://api.teamtailor.com/v1/jobs";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Token token=$token",
        "X-Api-Version: 20210218",
        "Content-Type: application/json"
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return "cURL Error: $error";
    }

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($status != 200) {
        return "API Request Failed with Status Code: $status\nResponse: $response";
    }

    $formattedResponse = json_decode($response, true);
    return json_encode($formattedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
