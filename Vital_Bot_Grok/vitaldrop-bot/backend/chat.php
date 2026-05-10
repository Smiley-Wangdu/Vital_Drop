<?php
/**
 * VitalDrop — chat.php
 * Receives user messages, calls Groq AI, logs to DB, returns reply.
 *
 * POST body (JSON):
 *   { "message": "...", "history": [ { "role": "user"|"assistant", "content": "..." }, ... ] }
 *
 * Response (JSON):
 *   { "reply": "..." }  |  { "error": "..." }
 */

// --- Headers ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

// --- Load dependencies ---
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";

// --- Parse input ---
$raw  = file_get_contents("php://input");
$body = json_decode($raw, true);

if (!$body || !isset($body["message"])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request. 'message' field is required."]);
    exit;
}

$userMessage = trim($body["message"]);
$history     = isset($body["history"]) && is_array($body["history"]) ? $body["history"] : [];

// --- Basic validation ---
if ($userMessage === "" || mb_strlen($userMessage) > 1000) {
    http_response_code(400);
    echo json_encode(["error" => "Message must be between 1 and 1000 characters."]);
    exit;
}

// --- Log to DB (non-blocking — chatbot works even if DB fails) ---
$logId = null;
$db = getDB();
if ($db) {
    try {
        $stmt = $db->prepare(
            "INSERT INTO chat_logs (user_message, created_at) VALUES (:msg, NOW())"
        );
        $stmt->execute([":msg" => $userMessage]);
        $logId = $db->lastInsertId();
    } catch (Exception $e) {
        error_log("[VitalDrop] DB insert failed: " . $e->getMessage());
    }
}

// --- Check API key ---
$apiKey = GROQ_API_KEY;
if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["error" => "Groq API key is not configured. Please set GROQ_API_KEY in backend/config.php"]);
    exit;
}

// ============================================================
// NURSE CLARA — System Prompt
// This defines the AI's entire personality and restrictions.
// ============================================================
$systemPrompt = <<<SYSTEM
You are Nurse Clara, a warm, professional, and knowledgeable nurse consultant for VitalDrop — a Blood Donation Management System.

YOUR PERSONALITY:
- Speak in a calm, caring, and reassuring tone — like a nurse speaking to a patient or donor.
- Be empathetic and encouraging. Blood donation can feel intimidating; help people feel comfortable.
- Be concise but thorough. Use clear, simple language that non-medical people can understand.
- Address the user respectfully. Use phrases like "I understand your concern", "That is a great question", or "Let me help you with that."
- Never be robotic or cold. Always sound human and compassionate.
- Do not use emojis in your responses.
- Use numbered lists or line breaks to organize steps clearly when explaining processes.

YOUR EXPERTISE — You ONLY answer questions about:
- Blood donation process (how to donate, what to expect, steps involved)
- Donor eligibility (age, weight, health conditions, medications, travel history)
- Blood types and compatibility (A, B, AB, O, positive, negative, universal donor/recipient)
- Blood requests and finding blood for patients
- Blood banks and donation centers (how to find them, what they do)
- General blood health information that relates to donation (not diagnosis)
- Post-donation care and recovery
- Types of donations (whole blood, platelets, plasma, double red cells)
- Frequency of donation and waiting periods
- Benefits of donating blood
- How to use the VitalDrop platform (register, request blood, find donors)
- Motivating and encouraging potential donors

STRICT RESTRICTIONS:
- Do NOT diagnose medical conditions or prescribe treatments.
- Do NOT answer questions about general medicine, diseases, fitness, diet, or unrelated health topics unless they directly relate to blood donation eligibility.
- Do NOT answer off-topic questions about technology, entertainment, politics, science, or anything unrelated to blood donation or VitalDrop.
- If asked about something outside your scope, respond EXACTLY with: "I am sorry, that falls outside my area of expertise as your VitalDrop nurse consultant. Is there anything related to blood donation or VitalDrop I can help you with?"

Always remember: You are Nurse Clara, a nurse consultant for VitalDrop. Stay in character at all times.
SYSTEM;

// --- Build messages for Groq ---
$messages = [["role" => "system", "content" => $systemPrompt]];

// Append conversation history for context
foreach ($history as $h) {
    if (isset($h["role"], $h["content"])) {
        $role = in_array($h["role"], ["user", "assistant"]) ? $h["role"] : "user";
        $messages[] = ["role" => $role, "content" => mb_substr($h["content"], 0, 800)];
    }
}

// Append current user message
$messages[] = ["role" => "user", "content" => $userMessage];

// --- Call Groq API ---
$payload = json_encode([
    "model"       => GROQ_MODEL,
    "max_tokens"  => GROQ_MAX_TOKENS,
    "temperature" => 0.65,
    "messages"    => $messages
]);

$ch = curl_init(GROQ_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey,
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// --- Handle cURL errors ---
if ($curlErr) {
    error_log("[VitalDrop] cURL error: " . $curlErr);
    http_response_code(502);
    echo json_encode(["error" => "Network error when contacting AI service. Please try again."]);
    exit;
}

// --- Parse Groq response ---
$result = json_decode($response, true);

if ($httpCode !== 200) {
    $errMsg = isset($result["error"]["message"]) ? $result["error"]["message"] : "AI service returned an error.";
    error_log("[VitalDrop] Groq API error ($httpCode): " . $errMsg);
    http_response_code(502);
    echo json_encode(["error" => $errMsg]);
    exit;
}

if (!isset($result["choices"][0]["message"]["content"])) {
    error_log("[VitalDrop] Unexpected Groq response: " . $response);
    http_response_code(502);
    echo json_encode(["error" => "Unexpected response from AI service."]);
    exit;
}

$botReply = trim($result["choices"][0]["message"]["content"]);

// --- Update DB log with bot reply ---
if ($db && $logId) {
    try {
        $stmt = $db->prepare("UPDATE chat_logs SET bot_reply = :reply WHERE id = :id");
        $stmt->execute([":reply" => $botReply, ":id" => $logId]);
    } catch (Exception $e) {
        error_log("[VitalDrop] DB update failed: " . $e->getMessage());
    }
}

// --- Return response ---
echo json_encode(["reply" => $botReply]);
