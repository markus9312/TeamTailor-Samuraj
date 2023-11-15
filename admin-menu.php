<?php
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
