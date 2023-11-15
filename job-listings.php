<?php
function display_wp_job_manager_listings() {
    $args = array(
        'post_type'      => 'job_listing', // The custom post type used by WP Job Manager
        'posts_per_page' => -1,  // Fetch all job listings
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<ul>';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo 'No job listings found.';
    }

    wp_reset_postdata(); // Reset the global post object
}

// Create a shortcode for easy use
function wp_job_manager_listings_shortcode() {
    ob_start();
    display_wp_job_manager_listings();
    return ob_get_clean();
}
add_shortcode('job_listings', 'wp_job_manager_listings_shortcode');
