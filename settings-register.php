<?php
// Register settings.
add_action('admin_init', 'coswift_register_settings');

function coswift_register_settings() {
    register_setting('coswift_options_group', 'coswift_api_token');
    add_settings_section('coswift_main_section', null, null, 'coswift-settings');
    add_settings_field('coswift_api_token', 'API Token', 'coswift_api_token_callback', 'coswift-settings', 'coswift_main_section');
}
