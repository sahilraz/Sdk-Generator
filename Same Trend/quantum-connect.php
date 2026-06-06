<?php
/**
 * =========================================================
 *  QUANTUM API: SAME TREND API CONNECTOR
 *  Created by: Devil Boy
 *  Website: QuantumApi
 *  All Rights Reserved.
 * =========================================================
 */
// quantum-connect.php
// This universal file connects to the API and fetches the latest issue data.

// Define common API base URL and API Key
$api_base_url = "https://quantum-api.ajibcomedy.workers.dev/";
$api_key = "YOUR_API_KEY";

if (!isset($game_param)) {
    die("Error: \$game_param is not defined.");
}

if (!isset($game_name)) {
    die("Error: \$game_name is not defined.");
}

$url = rtrim($api_base_url, '/') . '/' . urlencode($game_name) . '?game=' . urlencode($game_param) . '&api_key=' . urlencode($api_key);

$apidata = [];
$apiFetchOk = false;

$ch = curl_init($url);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

$origin_url = $protocol . "://" . $host;
$referer_url = $protocol . "://" . $host . $request_uri;

$headers = [
    "accept: application/json, text/plain, */*",
    "accept-language: en-GB,en-US;q=0.9,en;q=0.8,te;q=0.7",
    "priority: u=1, i",
    "sec-ch-ua: \"Chromium\";v=\"136\", \"Google Chrome\";v=\"136\", \"Not.A/Brand\";v=\"99\"",
    "sec-ch-ua-mobile: ?1",
    "sec-ch-ua-platform: \"Android\"",
    "sec-fetch-dest: empty",
    "sec-fetch-mode: cors",
    "sec-fetch-site: cross-site",
    "Origin: " . $origin_url,
    "Referer: " . $referer_url,
];

if (!empty($api_key) && $api_key !== "YOUR_API_KEY") {
    $headers[] = "x-api-key: " . $api_key;
}

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 10,
]);

// Execute request
$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Parse JSON response
if (!$error && $httpCode === 200 && is_string($response) && $response !== '') {
    $data = json_decode($response, true);
    if (is_array($data)) {
        if (isset($data['data']['list'][0]) && is_array($data['data']['list'][0])) {
            $apidata = $data['data']['list'][0];
            $apiFetchOk = true;
        } elseif (isset($data['list'][0]) && is_array($data['list'][0])) {
            $apidata = $data['list'][0];
            $apiFetchOk = true;
        }
    }
}
?>
