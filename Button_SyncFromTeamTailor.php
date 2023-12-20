<?php
// Button_SyncFromTeamTailor.php

defined('ABSPATH') or die('No script kiddies please!');

function coswift_sync_teamtailor() {
    $api_key = get_option('coswift_api_token');
    if (!$api_key) {
        echo '<div>API Key is not set.</div>';
        return;
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
    curl_close($ch);

    if ($response === false) {
        echo '<div>Error fetching data from TeamTailor.</div>';
        return;
    }

    $jobs = json_decode($response, true);
    $existing_ids = get_existing_job_ids();

    foreach ($jobs['data'] as $job) {
        $job_id = $job['id'];
        $post_id = coswift_get_post_id_by_job_id($job_id);

        $job_title = $job['attributes']['title'];
        $job_body = $job['attributes']['body'];
        $job_apply_iframe_url = $job['links']['careersite-job-apply-iframe-url'] ?? '';

        $post_content = $job_body;
        if ($job_apply_iframe_url) {
            $post_content .= "\n\n<iframe src='" . esc_url($job_apply_iframe_url) . "' width='100%' height='600' frameborder='0'></iframe>";
        }

        $post_data = [
            'post_type' => 'coswift_jobs',
            'post_title' => $job_title,
            'post_content' => $post_content,
            'post_status' => 'publish',
            'meta_input' => [
                '_coswift_job_id' => $job_id,
            ],
        ];

        if ($post_id) {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        } else {
            wp_insert_post($post_data);
        }

        if (($key = array_search($job_id, $existing_ids)) !== false) {
            unset($existing_ids[$key]);
        }
    }

    foreach ($existing_ids as $id) {
        $post_id = coswift_get_post_id_by_job_id($id);
        if ($post_id) {
            wp_delete_post($post_id, true);
        }
    }

    echo '<div>Sync completed successfully.</div>';
}

function coswift_get_post_id_by_job_id($job_id) {
    $query = new WP_Query([
        'post_type' => 'coswift_jobs',
        'meta_query' => [
            [
                'key' => '_coswift_job_id',
                'value' => $job_id,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => 1,
    ]);

    if ($query->have_posts()) {
        return $query->posts[0]->ID;
    }
    return null;
}

function get_existing_job_ids() {
    $query = new WP_Query([
        'post_type' => 'coswift_jobs',
        'posts_per_page' => -1,
    ]);

    $ids = [];
    foreach ($query->posts as $post) {
        $ids[] = get_post_meta($post->ID, '_coswift_job_id', true);
    }
    return $ids;
}

coswift_sync_teamtailor();
?>
