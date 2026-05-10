<?php
/**
 * VitalDrop Chatbot Widget — includes/chatbot_widget.php
 * ============================================================
 * Reusable include that renders the floating chatbot widget
 * ONLY for authenticated, non-admin users.
 *
 * HOW TO USE:
 *   Add this single line before </body> on any authenticated page:
 *
 *   <?php include __DIR__ . '/../includes/chatbot_widget.php'; ?>
 *
 *   (Adjust the relative path depth as needed, e.g. same-level
 *   pages use __DIR__ . '/../../includes/chatbot_widget.php')
 *
 * WHAT THIS FILE DOES:
 *   1. Checks PHP session — exits silently if user is not logged in
 *   2. Excludes admin accounts (keeps chatbot for regular users only)
 *   3. Injects the chatbot CSS <link> once
 *   4. Renders the full widget HTML
 *   5. Injects the chatbot <script> with the correct backend URL
 *
 * SESSION LOGIC:
 *   - $_SESSION['user_id'] must be set (main login system)
 *   - $_SESSION['user_role'] === 'admin' causes the widget to be hidden
 *
 * BACKEND:
 *   Calls /Vital_Drop/backend/chatbot.php — session-secured,
 *   integrated into main project (not the standalone bot folder).
 * ============================================================
 */

// ─── Ensure session is started ───────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── VISIBILITY GATE ─────────────────────────────────────────
// Rule 1: User must be logged in
if (!isset($_SESSION['user_id'])) {
    return; // Silent exit — widget not rendered for guests
}

// Rule 2: Admins do not see the user-facing chatbot assistant
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    return; // Silent exit — admins use a different panel
}

// ─── RESOLVE WEB PATHS ───────────────────────────────────────
// All paths are absolute web paths so they work regardless of
// which subdirectory the including page lives in (user/, admin/, auth/).
$projectRoot = '/Vital_Drop';
$chatbotCss  = $projectRoot . '/assets/css/chatbot.css';
$chatbotJs   = $projectRoot . '/assets/js/chatbot.js';

// Session-secured backend endpoint (main project, not Vital_Bot_Grok folder)
$chatbotApi  = $projectRoot . '/backend/chatbot.php';
?>

<!-- ============================================================
     VITALDROP CHATBOT WIDGET
     Session-gated: visible only to authenticated non-admin users.
     CSS/JS loaded once per page via absolute web paths.
     ============================================================ -->

<!-- Chatbot CSS (scoped to #vitaldrop-widget — no global conflicts) -->
<link rel="stylesheet" href="<?php echo $chatbotCss; ?>">

<!-- Chatbot Widget Root Element
     data-backend-url is read by chatbot.js to make API calls.
     This avoids hardcoding the path in JavaScript. -->
<div id="vitaldrop-widget" data-backend-url="<?php echo $chatbotApi; ?>">

    <!-- ── Toggle Button (the floating red chat icon) ── -->
    <button id="vd-toggle-btn" aria-label="Open VitalDrop Chat Assistant">
        <!-- Chat icon (shown when closed) -->
        <span id="vd-icon-open">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </span>
        <!-- X icon (shown when open) -->
        <span id="vd-icon-close" style="display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </span>
    </button>

    <!-- ── Chat Window (hidden by default, expands on button click) ── -->
    <div id="vd-chat-window" aria-hidden="true" role="dialog" aria-label="VitalDrop Chat Assistant">

        <!-- Header -->
        <div id="vd-header">
            <div id="vd-header-left">
                <!-- Blood drop logo -->
                <div id="vd-logo-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C12 2 5 10.5 5 15a7 7 0 0 0 14 0C19 10.5 12 2 12 2z"/>
                    </svg>
                </div>
                <div>
                    <div id="vd-title">VitalDrop Assistant</div>
                    <div id="vd-subtitle">Nurse Clara · Online</div>
                </div>
            </div>
            <!-- Close button inside chat window -->
            <button id="vd-close-btn" aria-label="Close chat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Messages area — populated by chatbot.js -->
        <div id="vd-messages" role="log" aria-live="polite" aria-label="Chat messages"></div>

        <!-- Quick-reply chips for common questions -->
        <div id="vd-quick-replies">
            <button class="vd-quick-btn" data-msg="How do I donate blood?">Donate Blood</button>
            <button class="vd-quick-btn" data-msg="How do I find blood?">Find Blood</button>
            <button class="vd-quick-btn" data-msg="Am I eligible to donate blood?">Eligibility</button>
            <button class="vd-quick-btn" data-msg="How do I find a blood bank?">Blood Banks</button>
        </div>

        <!-- Text input area -->
        <div id="vd-input-area">
            <input
                type="text"
                id="vd-input"
                placeholder="Ask me anything about blood donation..."
                autocomplete="off"
                maxlength="500"
                aria-label="Type your message"
            />
            <button id="vd-send-btn" aria-label="Send message">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>

    </div><!-- #vd-chat-window -->

</div><!-- #vitaldrop-widget -->

<!-- Chatbot JS — self-initializing IIFE, loaded after DOM is ready.
     Reads data-backend-url from #vitaldrop-widget to avoid hardcoded paths. -->
<script src="<?php echo $chatbotJs; ?>"></script>
