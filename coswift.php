<?php
/**
 * Plugin Name: CoSwift
 * Description: A plugin to integrate with TeamTailor API.
 * Version: 1.0
 * Author: dotMavriQ
 * Author URI: Your Website
 */

// Hook for adding admin menus
add_action('admin_menu', 'coswift_menu');

// Action function for the above hook
function coswift_menu() {
    // Add a new submenu under Settings
    add_options_page('CoSwift Settings', 'CoSwift', 'manage_options', 'coswift', 'coswift_settings_page');
}

// Function to display the plugin admin page
function coswift_settings_page() {
    // Save the API key if the form has been submitted
    if (isset($_POST['save_api_key']) && isset($_POST['api_key'])) {
        // Perform any necessary security checks before saving, like nonce verification
        update_option('coswift_api_token', sanitize_text_field($_POST['api_key']));
        // You should add an admin notice here to confirm that the key was saved
    }

    ?>
    <div class="wrap">
        <h2>CoSwift Settings</h2>

        <form method="post" action="">
            <?php settings_fields('coswift-options-group'); ?>
            <?php do_settings_sections('coswift-options-group'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td>
                        <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr(get_option('coswift_api_token')); ?>" maxlength="40" />
                        <input type="submit" name="save_api_key" class="button button-primary" value="Save API Key" />
                    </td>
                </tr>
            </table>
        </form>
        <p>
            <button id="test_api" class="button action">Test API</button>
            <button id="sync_teamtailor" class="button action">Sync from TeamTailor</button>
        </p>

        <!-- The area where the formatted JSON response will be displayed -->
        <div id="coswift-json-response" style="max-height: 400px; overflow-y: scroll; background-color: #f4f4f4; border: 1px solid #ddd; padding: 10px; margin-top: 15px; white-space: pre;"></div>
    </div> 

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#test_api').on('click', function() {
            var data = {
                'action': 'coswift_test_api',
                'api_key': $('#api_key').val()
            };

            $.post(ajaxurl, data, function(response) {
                var jsonResponse;
                try {
                    jsonResponse = JSON.stringify(JSON.parse(response), null, 4);
                } catch (e) {
                    jsonResponse = 'Error parsing JSON: ' + e.message;
                }
                $('#coswift-json-response').text(jsonResponse);
            });
        });

        // Handle Sync from TeamTailor button click
        $('#sync_teamtailor').on('click', function() {
            var data = {
                'action': 'coswift_sync_teamtailor',
                // Add any other data you need to send
            };

            $.post(ajaxurl, data, function(response) {
                var jsonResponse;
                try {
                    jsonResponse = JSON.stringify(JSON.parse(response), null, 4);
                } catch (e) {
                    jsonResponse = 'Error parsing JSON: ' + e.message;
                }
                $('#coswift-json-response').text(jsonResponse);
            });
        });
    });
    </script>
    <?php
}

// Register and define the settings with a validation callback
add_action('admin_init', 'coswift_register_settings');

function coswift_register_settings() {
    // Register a new setting for "CoSwift" page with a validation callback
    register_setting('coswift-options-group', 'coswift_api_token', 'coswift_validate_api_key');
}

// Validation callback function for the API key
function coswift_validate_api_key($input) {
    if (strlen($input) != 40) {
        add_settings_error(
            'api_key',
            'api_key_error',
            'Error: API Key must be exactly 40 characters long.',
            'error'
        );
        return get_option('coswift_api_token'); // Return the existing value to prevent saving invalid data
    }
    return $input;
}

// AJAX action hook for testing API using cURL
add_action('wp_ajax_coswift_test_api', 'coswift_test_api_callback');

function coswift_test_api_callback() {
    $api_key = sanitize_text_field($_POST['api_key']);
    $result = fetch_coswift_job_listings_with_curl($api_key);
    echo $result;
    wp_die(); // this is required to terminate immediately and return a proper response
}

// Function to fetch job listings from the TeamTailor API using cURL
function fetch_coswift_job_listings_with_curl($api_key) {
    if (!$api_key) {
        return 'API Key is not set.';
    }

    $url = "https://api.teamtailor.com/v1/jobs";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Token token={$api_key}",
        "X-Api-Version: 20210218",
        "Content-Type: application/json"
    ));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        return "cURL Error: {$error}";
    }

    if ($status !== 200) {
        return "API Request Failed with Status Code: {$status}";
    }

    return $response; // return raw JSON response
}

// Enqueue the JavaScript file for admin
add_action('admin_enqueue_scripts', 'coswift_enqueue_admin_scripts');

function coswift_enqueue_admin_scripts() {
    wp_enqueue_script('coswift-admin-js', plugins_url('/js/scripts.js', __FILE__), array('jquery'), null, true );
    wp_localize_script('coswift-admin-js', 'ajaxurl', admin_url('admin-ajax.php'));
}
// Function hooked to wp_ajax_ to handle the AJAX request
add_action('wp_ajax_coswift_sync_jobs', 'coswift_sync_jobs_callback');

function coswift_sync_jobs_callback() {
    $api_key = sanitize_text_field($_POST['api_key']);
    $job_data = fetch_jobs_from_teamtailor($api_key);
    $job_listings = json_decode($job_data, true);

    if (!empty($job_listings)) {
        foreach ($job_listings['data'] as $job) {
            $post_id = wp_insert_post([
                'post_title'  => wp_strip_all_tags($job['attributes']['title']),
                'post_type'   => 'coswift_jobs',
                'post_status' => 'publish',
                'meta_input'  => [
                    'teamtailor_id' => $job['id'],
                    // Add other meta data as needed
                ],
            ]);

            // Check for errors
            if (is_wp_error($post_id)) {
                // Handle the error accordingly
            }
        }
        wp_send_json_success(['message' => 'Jobs synchronized successfully.']);
    } else {
        wp_send_json_error(['message' => 'No jobs found or an error occurred.']);
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}

function fetch_jobs_from_teamtailor($api_key) {
    if (empty($api_key)) {
        return 'API Key is not set.';
    }

    $url = "https://api.teamtailor.com/v1/jobs"; // Replace with the actual API endpoint

    $headers = array(
        "Authorization: Token token={$api_key}",
        "X-Api-Version: 20210218",
        "Content-Type: application/json"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // This is not recommended for production

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "cURL Error: {$error_msg}";
    }

    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status_code !== 200) {
        return "API Request Failed with Status Code: {$status_code}";
    }

    return $response; // Return the raw JSON response
}
// Register a custom post type for the jobs
function coswift_register_custom_post_type() {
    register_post_type('coswift_jobs', [
        'labels' => [
            'name' => __('CoSwift Jobs'),
            // Other labels as necessary
        ],
        'public' => true,
        // Other arguments as necessary
    ]);
}
add_action('init', 'coswift_register_custom_post_type');

function coswift_list_jobs_menu() {
    add_submenu_page(
        'edit.php?post_type=coswift_jobs',
        'List CoSwift Jobs',
        'All Jobs',
        'manage_options',
        'coswift_jobs_list',
        'coswift_jobs_list_callback'
    );
}

add_action('admin_menu', 'coswift_list_jobs_menu');

function coswift_jobs_list_callback() {
    // Code to list all the jobs
    // WP provides a default view, but you can customize this as needed
}

