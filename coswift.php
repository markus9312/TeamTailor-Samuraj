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
    ?>
    <div class="wrap">
        <h2>CoSwift Settings</h2>
        <form method="post">
            <?php
                settings_fields('coswift_options_group');
                do_settings_sections('coswift-settings');
                submit_button('Sync');
            ?>
        </form>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Added styled div for JSON output
                echo '<div class="json-output">';
                echo fetch_coswift_job_listings();
                echo '</div>';
            }
        ?>
    </div>
    <style>
        /* CSS for the JSON output box */
        .json-output {
            max-height: 400px; /* Adjust height as needed */
            max-width: 100%; /* Adjust width as needed */
            overflow: auto; /* Adds scrollbar when content overflows */
            background-color: #f9f9f9; /* Optional: background color */
            border: 1px solid #ddd; /* Optional: border */
            padding: 10px; /* Optional: inner spacing */
            font-family: monospace; /* Optional: monospace font for better readability */
            white-space: pre-wrap
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
