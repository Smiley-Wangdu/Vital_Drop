<?php
/**
 * VitalDrop — test_groq.php
 * Run this to diagnose Groq API connection issues.
 * Visit: http://localhost/vitaldrop-bot/backend/test_groq.php
 * DELETE this file after testing.
 */

require_once __DIR__ . "/config.php";

echo "<h2>VitalDrop — Groq API Diagnostic</h2>";
echo "<pre>";

// 1. Check API Key
$apiKey = GROQ_API_KEY;
echo "1. API Key set: " . (empty($apiKey) ? "NO - MISSING" : "YES (starts with: " . substr($apiKey, 0, 8) . "...)") . "\n\n";

// 2. Check cURL
echo "2. cURL enabled: " . (function_exists("curl_init") ? "YES" : "NO - Enable in php.ini") . "\n\n";

// 3. Check PHP version
echo "3. PHP version: " . PHP_VERSION . "\n\n";

// 4. Try actual Groq API call
echo "4. Testing Groq API call...\n";

if (empty($apiKey)) {
    echo "   SKIPPED - No API key set.\n";
} elseif (!function_exists("curl_init")) {
    echo "   SKIPPED - cURL not available.\n";
} else {
    $payload = json_encode([
        "model" => GROQ_MODEL,
        "max_tokens" => 50,
        "messages" => [
            ["role" => "user", "content" => "Say hello in one sentence."]
        ]
    ]);

    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey,
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    echo "   HTTP Status: " . $httpCode . "\n";

    if ($curlErr) {
        echo "   cURL Error: " . $curlErr . "\n";
    } else {
        $result = json_decode($response, true);
        if ($httpCode === 200 && isset($result["choices"][0]["message"]["content"])) {
            echo "   SUCCESS! Groq replied: " . $result["choices"][0]["message"]["content"] . "\n";
        } else {
            echo "   FAILED. Raw response:\n";
            echo "   " . $response . "\n";
        }
    }
}

echo "</pre>";