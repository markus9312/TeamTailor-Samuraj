<?php
// Button_SyncFromTeamTailor.php

// Ensure WordPress is loaded
defined('ABSPATH') or die('No script kiddies please!');

function coswift_sync_teamtailor() {
    $api_key = get_option('coswift_api_token');
    if (!$api_key) {
        echo '<div>API Key is not set.</div>';
        return;
    }

    $url = "https://api.teamtailor.com/v1/jobs"; // Adjust the API URL as needed
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

    foreach ($jobs['data'] as $job) {
        $job_title = $job['attributes']['title'];
        $job_body = $job['attributes']['body'];
        $job_apply_url = $job['links']['careersite-job-apply-iframe-url'];

        $post_content = $job_body;
        if (!empty($job_apply_url)) {
            $post_content .= "\n\n" . '<iframe src="' . esc_url($job_apply_url) . '"></iframe>';
        }

        wp_insert_post([
            'post_type' => 'coswift_jobs',
            'post_title' => $job_title,
            'post_content' => $post_content,
            'post_status' => 'publish',
            // Add other post fields as needed
        ]);
    }

    echo '<div>Sync completed successfully.</div>';
}

coswift_sync_teamtailor();
?>
