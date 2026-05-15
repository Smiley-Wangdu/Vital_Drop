<?php
$apiKey = "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";
$url = "https://api.groq.com/openai/v1/chat/completions";
$payload = json_encode([
    "model" => "llama-3.3-70b-versatile",
    "messages" => [["role" => "user", "content" => "Hello"]]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey,
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Curl Error: $curlErr\n";
echo "Response: $response\n";
