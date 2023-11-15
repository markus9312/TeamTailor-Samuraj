<?php
// Helper functions.
function coswift_api_token_callback() {
    $token = get_option('coswift_api_token');
    echo '<input type="text" id="coswift_api_token" name="coswift_api_token" value="' . esc_attr($token) . '" style="width: 330px;" />';
}