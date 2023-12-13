<?php
/**
 * Plugin Name: CoSwift
 * Description: Integration with TeamTailor for WordPress.
 * Version: 0.3
 * Author: Jonatan Jansson
 * URI: https://github.com/dotMavriQ/CoSwift
 */

// Ensure WordPress is loaded
defined('ABSPATH') or die('No script kiddies please!');

// Include other PHP files
include_once(plugin_dir_path(__FILE__) . 'Button_Form_SaveToken.php');
include_once(plugin_dir_path(__FILE__) . 'Button_TestAPI.php');

// Add plugin to the admin menu
function coswift_add_to_menu() {
    add_menu_page('CoSwift Settings', 'CoSwift', 'manage_options', 'coswift', 'coswift_settings_page');
}
add_action('admin_menu', 'coswift_add_to_menu');

function coswift_register_settings() {
    register_setting('coswift-settings-group', 'coswift_api_token');
}
add_action('admin_init', 'coswift_register_settings');
// 
function coswift_settings_page() {
    ?>
    <div class="wrap">
        <h1>CoSwift Settings</h1>
        <?php coswift_save_token_form(); ?>

        <form method="post" action="">
            <input type="submit" name="test_api" class="button button-primary" value="Test API">
        </form>

        <?php
        if (isset($_POST['test_api'])) {
            coswift_test_api_call();
        }
        ?>
    </div>
    <form method="post" action="">
        <input type="submit" name="sync_teamtailor" class="button button-primary" value="Sync from TeamTailor">
    </form>

    <?php
    if (isset($_POST['sync_teamtailor'])) {
        include(plugin_dir_path(__FILE__) . 'Button_SyncFromTeamTailor.php');
    }
}

function coswift_test_api_call() {
    $api_key = get_option('coswift_api_token');
    if (!$api_key) {
        echo '<div>API Key is not set.</div>';
        return;
    }

    // cURL request to TeamTailor API
    $url = "https://api.teamtailor.com/v1/jobs"; // Replace with the actual API URL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Token token={$api_key}",
        "X-Api-Version: 20210218",
        "Content-Type: application/json"
    ));

    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        echo "<div>cURL Error: {$error}</div>";
    } else {
        // Decode and then re-encode the JSON response with pretty print and unescaped slashes
        $decodedResponse = json_decode($response);
        $prettyResponse = json_encode($decodedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Output the formatted JSON in the div
        echo '<div id="coswift-api-response" style="white-space: pre; max-height: 400px; overflow-y: scroll; background-color: #f4f4f4; border: 1px solid #ddd; padding: 10px; margin-top: 15px;">';
        echo htmlspecialchars($prettyResponse);
        echo '</div>';
    }

    curl_close($ch);
}
function coswift_register_custom_post_type() {
    register_post_type('coswift_jobs', [
        'labels' => [
            'name' => 'CoSwift Jobs',
            'singular_name' => 'CoSwift Job'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        // Add other necessary arguments as needed
    ]);
}
add_action('init', 'coswift_register_custom_post_type');

function coswift_add_job_metaboxes() {
    add_meta_box(
        'coswift_job_details',           // Unique ID for the metabox
        'Job Details',                   // Title of the metabox
        'coswift_job_details_callback',  // Callback function
        'coswift_jobs',                  // Post type
        'normal',                        // Context (where on the screen)
        'high'                           // Priority (order on the screen)
    );
}
add_action('add_meta_boxes', 'coswift_add_job_metaboxes');

function coswift_job_details_callback($post) {
    // Add nonce for security and authentication
    wp_nonce_field(plugin_basename(__FILE__), 'coswift_job_nonce');
    
    // Retrieve the current values for your custom meta fields
    $coswift_job_id = get_post_meta($post->ID, '_coswift_job_id', true);
    $coswift_job_type = get_post_meta($post->ID, '_coswift_job_type', true);

    // Metabox HTML
    echo '<label for="coswift_job_id">Job ID:</label>';
    echo '<input type="text" id="coswift_job_id" name="coswift_job_id" value="' . esc_attr($coswift_job_id) . '" size="25" />';

    echo '<label for="coswift_job_type">Job Type:</label>';
    echo '<input type="text" id="coswift_job_type" name="coswift_job_type" value="' . esc_attr($coswift_job_type) . '" size="25" />';
}
function coswift_save_job_metaboxes($post_id) {
    // Check nonce, autosave, and user permissions
    if (!isset($_POST['coswift_job_nonce']) || !wp_verify_nonce($_POST['coswift_job_nonce'], plugin_basename(__FILE__))) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if ('coswift_jobs' != $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Save or update the meta field in the database
    if (isset($_POST['coswift_job_id'])) {
        update_post_meta($post_id, '_coswift_job_id', sanitize_text_field($_POST['coswift_job_id']));
    }
    if (isset($_POST['coswift_job_type'])) {
        update_post_meta($post_id, '_coswift_job_type', sanitize_text_field($_POST['coswift_job_type']));
    }
}
add_action('save_post', 'coswift_save_job_metaboxes');
