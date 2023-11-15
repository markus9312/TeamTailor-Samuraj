<?php
// Function to fetch job listings.
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
        return "API Request Failed with Status Code: $status\nResponse: $response";
    }

    $formattedResponse = json_decode($response, true);
    return json_encode($formattedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
