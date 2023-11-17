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

            // Custom query to fetch job listings
            $job_listings = new WP_Query([
                'post_type' => 'awsm_job_openings', // Using the correct post type
                'posts_per_page' => -1, // Adjust as needed
            ]);

            // Start the table for job listings
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Job Title</th>';
            echo '<th>Job ID</th>';
            echo '<th>Author</th>';
            echo '<th>Applications</th>';
            echo '<th>Expiry</th>';
            echo '<th>Views</th>';
            echo '<th>Conversion</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Loop through the job listings
            while ($job_listings->have_posts()) {
                $job_listings->the_post();
                $post_id = get_the_ID();
                echo '<tr>';
                echo '<td>' . get_the_title() . '</td>';
                echo '<td>' . $post_id . '</td>';
                echo '<td>' . get_the_author() . '</td>';
                // For Applications, Expiry, Views, Conversion, use get_post_meta() if these are stored as post meta.
                echo '<td>' . get_post_meta($post_id, 'applications_meta_key', true) . '</td>'; // Replace 'applications_meta_key' with the actual meta key.
                echo '<td>' . get_post_meta($post_id, 'expiry_meta_key', true) . '</td>'; // Replace 'expiry_meta_key'.
                echo '<td>' . get_post_meta($post_id, 'views_meta_key', true) . '</td>'; // Replace 'views_meta_key'.
                echo '<td>' . get_post_meta($post_id, 'conversion_meta_key', true) . '</td>'; // Replace 'conversion_meta_key'.
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';

            // Reset Post Data
            wp_reset_postdata();
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
        /* Add additional CSS styles as needed */
    </style>
    <?php
}
?>
