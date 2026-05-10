<?php
/**
 * VitalDrop Chatbot Backend — backend/chatbot.php
 * ============================================================
 * INTEGRATION: Session-secured chatbot API endpoint.
 *
 * - Uses the main project's session system (includes/session.php)
 * - Uses the main project's DB connection (config/db.php)
 * - Calls Groq AI API with Nurse Clara personality
 * - Logs every conversation turn to the chat_logs table
 *
 * POST body (JSON):
 *   { "message": "...", "history": [ { "role": "user"|"assistant", "content": "..." }, ... ] }
 *
 * Response (JSON):
 *   { "reply": "..." }  or  { "error": "..." }
 *
 * REQUIREMENTS before first use:
 *   Run the chat_logs table migration in phpMyAdmin:
 *   /Vital_Drop/Vital_Bot_Grok/vitaldrop-bot/database/schema.sql
 * ============================================================
 */

// ─── Load Core Dependencies ──────────────────────────────────
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

// ─── SECURITY GATE: Only authenticated users may call this ───
// Uses the existing session system. isLoggedIn() checks $_SESSION['user_id'].
if (!isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Authentication required. Please log in.']);
    exit;
}

// ─── Response Headers ────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Handle CORS preflight (same-origin in production)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// ============================================================
// GROQ API CONFIGURATION
// ============================================================
// Option A (Recommended): Set via environment variable.
//   In XAMPP's httpd.conf or .htaccess add:
//     SetEnv GROQ_API_KEY "gsk_..."
// Option B: Replace the empty string in the ?: fallback below.
//   NEVER commit a real key to a public repository.
// ============================================================
define('VD_GROQ_API_KEY',    getenv('GROQ_API_KEY') ?: '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');
define('VD_GROQ_API_URL',    'https://api.groq.com/openai/v1/chat/completions');
define('VD_GROQ_MODEL',      'llama-3.3-70b-versatile');
define('VD_GROQ_MAX_TOKENS', 600);

// ─── Parse Input ─────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body || !isset($body['message'])) {
    http_response_code(400);
    echo json_encode(['error' => "Invalid request body. 'message' field is required."]);
    exit;
}

$userMessage = trim($body['message']);
$history     = (isset($body['history']) && is_array($body['history'])) ? $body['history'] : [];

// Validate message length
if ($userMessage === '' || mb_strlen($userMessage) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Message must be between 1 and 1000 characters.']);
    exit;
}

// ─── Log to DB (non-blocking — chatbot works even if logging fails) ───
// $pdo is provided by config/db.php (main project connection)
$logId = null;
try {
    $stmt = $pdo->prepare(
        'INSERT INTO chat_logs (user_message, created_at) VALUES (:msg, NOW())'
    );
    $stmt->execute([':msg' => $userMessage]);
    $logId = $pdo->lastInsertId();
} catch (Exception $e) {
    // Non-fatal — chatbot still works without DB logging
    error_log('[VitalDrop Chatbot] DB insert failed: ' . $e->getMessage());
}

// ─── Validate API Key ────────────────────────────────────────
$apiKey = VD_GROQ_API_KEY;
if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'Groq API key is not configured. Set GROQ_API_KEY in backend/chatbot.php']);
    exit;
}

// ============================================================
// NURSE CLARA — System Prompt
// Defines the AI's entire personality, scope, and restrictions.
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

// ─── Build Message Array for Groq ────────────────────────────
$messages = [['role' => 'system', 'content' => $systemPrompt]];

// Append conversation history so AI has context for multi-turn chat
foreach ($history as $h) {
    if (isset($h['role'], $h['content'])) {
        $role       = in_array($h['role'], ['user', 'assistant']) ? $h['role'] : 'user';
        $messages[] = ['role' => $role, 'content' => mb_substr($h['content'], 0, 800)];
    }
}

// Append current user message
$messages[] = ['role' => 'user', 'content' => $userMessage];

// ─── Call Groq API ───────────────────────────────────────────
$payload = json_encode([
    'model'       => VD_GROQ_MODEL,
    'max_tokens'  => VD_GROQ_MAX_TOKENS,
    'temperature' => 0.65,
    'messages'    => $messages,
]);

$ch = curl_init(VD_GROQ_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// ─── Handle cURL Errors ───────────────────────────────────────
if ($curlErr) {
    error_log('[VitalDrop Chatbot] cURL error: ' . $curlErr);
    http_response_code(502);
    echo json_encode(['error' => 'Network error when contacting AI service. Please try again.']);
    exit;
}

// ─── Parse Groq Response ─────────────────────────────────────
$result = json_decode($response, true);

if ($httpCode !== 200) {
    $errMsg = $result['error']['message'] ?? 'AI service returned an error.';
    error_log('[VitalDrop Chatbot] Groq API error (' . $httpCode . '): ' . $errMsg);
    http_response_code(502);
    echo json_encode(['error' => $errMsg]);
    exit;
}

if (!isset($result['choices'][0]['message']['content'])) {
    error_log('[VitalDrop Chatbot] Unexpected Groq response: ' . $response);
    http_response_code(502);
    echo json_encode(['error' => 'Unexpected response from AI service.']);
    exit;
}

$botReply = trim($result['choices'][0]['message']['content']);

// ─── Update DB Log with Bot Reply ────────────────────────────
if ($logId) {
    try {
        $stmt = $pdo->prepare('UPDATE chat_logs SET bot_reply = :reply WHERE id = :id');
        $stmt->execute([':reply' => $botReply, ':id' => $logId]);
    } catch (Exception $e) {
        error_log('[VitalDrop Chatbot] DB update failed: ' . $e->getMessage());
    }
}

// ─── Return AI Reply ─────────────────────────────────────────
echo json_encode(['reply' => $botReply]);
