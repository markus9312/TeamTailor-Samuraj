<?php
// Ensure WordPress is loaded
defined('ABSPATH') or die('No script kiddies please!');

function coswift_save_token_form() {
    // Check if the form has been submitted
    if (isset($_POST['coswift_api_token'])) {
        $api_token = sanitize_text_field($_POST['coswift_api_token']);

        // Validate the API token
        if (strlen($api_token) == 40) {
            // Save the token
            update_option('coswift_api_token', $api_token);
            echo '<div class="notice notice-success is-dismissible"><p>API Token saved.</p></div>';
        } else {
            // Display error message
            echo '<div class="notice notice-error is-dismissible"><p>API Token must be exactly 40 characters long.</p></div>';
        }
    }

    // Form HTML
    ?>
    <div class="coswift-token-form">
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Token:</th>
                    <td><input type="text" name="coswift_api_token" value="<?php echo esc_attr(get_option('coswift_api_token')); ?>" style="width: 333px;" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// You can call this function in your settings page function to display the form
