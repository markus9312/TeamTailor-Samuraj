add_action('admin_head', function () {
    global $post_type;
    if ('coswift_jobs' == $post_type) {
        add_filter('acf/settings/remove_wp_meta_box', '__return_false');
    }
});
