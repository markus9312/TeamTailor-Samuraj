<?php
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
            // Here we add the WP Job Openings shortcode
            echo '<div class="wp-job-openings-list">';
            echo do_shortcode('[awsmjobs]');
            echo '</div>';
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
        .wp-job-openings-list {
            margin-top: 20px;
        }
    </style>
    <?php
}
