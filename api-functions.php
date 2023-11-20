<?php
// api-functions.php

// Function to fetch job listings from the TeamTailor API
function fetch_coswift_job_listings() {
    $token = get_option('coswift_api_token');
    if (!$token) {
        return 'API Token is not set.';
    }

    $url = "https://api.teamtailor.com/v1/jobs";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Token token=$token",
        "X-Api-Version: 20210218",
        "Content-Type: application/json"
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return "cURL Error: $error";
    }

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($status != 200) {
        return "API Request Failed with Status Code: $status";
    }

    return $response; // Return the raw response
}

// Function to sync job listings with the WordPress custom post types
function coswift_sync_job_listings() {
    $response = fetch_coswift_job_listings();
    if (is_string($response)) {
        $jobs = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "JSON Decode Error: " . json_last_error_msg();
        }

        foreach ($jobs['data'] as $job) {
            $teamtailor_job_id = $job['id'];
            $job_title = $job['attributes']['title'];
            $job_description = $job['attributes']['body'] ?? 'No description available';

            $existing_post_id = coswift_get_post_by_teamtailor_id($teamtailor_job_id);

            $post_data = [
                'post_title'    => sanitize_text_field($job_title),
                'post_content'  => wp_kses_post($job_description),
                'post_status'   => 'publish',
                'post_type'     => 'awsm_job_openings',
                'meta_input'    => [
                    'teamtailor_job_id' => $teamtailor_job_id,
                    // Add other meta data here as needed
                ],
            ];

            if ($existing_post_id) {
                $post_data['ID'] = $existing_post_id;
                wp_update_post($post_data);
            } else {
                wp_insert_post($post_data);
            }
        }

        return 'Jobs synchronized successfully.';
    } else {
        return $response; // In case of an error in the API call
    }
}

function coswift_get_post_by_teamtailor_id($teamtailor_job_id) {
    $query = new WP_Query([
        'post_type' => 'awsm_job_openings',
        'meta_key'  => 'teamtailor_job_id',
        'meta_value'=> $teamtailor_job_id,
        'posts_per_page' => 1
    ]);

    if ($query->have_posts()) {
        $query->the_post();
        return get_the_ID();
    }

    return false;
}
