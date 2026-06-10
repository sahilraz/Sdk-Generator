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

// Log all PHP errors to quantum-error.log
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/quantum-error.log');

// Custom logger
function quantumLog($message) {
    error_log("[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL, 3, __DIR__ . '/quantum-error.log');
}

// Catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();

    if ($error !== null) {
        quantumLog(
            "FATAL ERROR | Type: {$error['type']} | Message: {$error['message']} | File: {$error['file']} | Line: {$error['line']}"
        );
    }
});

$conn = new mysqli("localhost", "root", "", "quantum_api");
if ($conn->connect_error) {
    quantumLog("DATABASE CONNECTION FAILED: " . $conn->connect_error);
    die("Database connection failed.");
}

$conn->query("
    CREATE TABLE IF NOT EXISTS game_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        operation_mode ENUM('api','manual') NOT NULL DEFAULT 'api'
    )
");

$result = $conn->query("SELECT COUNT(*) AS total FROM game_settings");
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    $conn->query("INSERT INTO game_settings (operation_mode) VALUES ('api')");
}

$modeQuery = $conn->query("SELECT operation_mode FROM game_settings WHERE id = 1 LIMIT 1");
$modeRow = ($modeQuery && $modeQuery->num_rows > 0) ? $modeQuery->fetch_assoc() : null;
$operation_mode = $modeRow['operation_mode'] ?? 'api';
$toggle = ($operation_mode === 'manual') ? 1 : 0;

// Define common API base URL and API Key
$api_base_url = "https://quantum-api.ajibcomedy.workers.dev/";
$api_key = "YOUR_API_KEY";



if (!isset($game_param)) {
    quantumLog("game_param is not defined.");
    die("Error: \$game_param is not defined.");
}

if (!isset($game_name)) {
    quantumLog("game_name is not defined.");
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

if ($error) {
    quantumLog("CURL ERROR: " . $error);
}

if ($httpCode != 200) {
    quantumLog("API HTTP ERROR: HTTP {$httpCode} | URL: {$url}");
}

curl_close($ch);

// Parse JSON response
if (!$error && $httpCode === 200 && is_string($response) && $response !== '') {
    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        quantumLog(
            "JSON ERROR: " . json_last_error_msg() .
            " | Response: " . substr($response, 0, 1000)
        );
    }

    if (is_array($data)) {
        if (isset($data['data']['list'][0]) && is_array($data['data']['list'][0])) {
            $apidata = $data['data']['list'][0];
            $apiFetchOk = true;
        } elseif (isset($data['list'][0]) && is_array($data['list'][0])) {
            $apidata = $data['list'][0];
            $apiFetchOk = true;
        }
    }

    if (!$apiFetchOk) {
        quantumLog(
            "INVALID API RESPONSE STRUCTURE | URL: {$url} | Response: " .
            substr($response ?? '', 0, 1000)
        );
    }
}
?>
