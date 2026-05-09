<?php
/**
 * VitalDrop Chatbot Widget — Reusable Include
 * ============================================================
 * INTEGRATION: This file renders the floating chatbot widget
 * ONLY when the user is logged in (session-based visibility).
 *
 * USAGE: Include this file at the bottom of any page's <body>,
 *        BEFORE the closing </body> tag:
 *
 *   <?php include __DIR__ . '/../includes/chatbot_widget.php'; ?>
 *
 * REQUIREMENTS:
 *   - session_start() must be called before including this file.
 *   - The chatbot CSS and JS are loaded automatically.
 *
 * SESSION CHECK:
 *   - Uses $_SESSION['user_id'] which is set by the existing
 *     login system in auth/login.php for both users and admins.
 * ============================================================
 */

// Ensure session is started (safe to call multiple times)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── CHATBOT VISIBILITY GATE ────────────────────────────────
// Only render the widget if user is authenticated.
// This checks the same session variable used by the existing
// login system (auth/login.php sets $_SESSION['user_id']).
if (!isset($_SESSION['user_id'])) {
    return; // Exit silently — no chatbot for unauthenticated users
}

// ─── RESOLVE PATHS ──────────────────────────────────────────
// Calculate the base path to the project root relative to the
// web server, so CSS/JS/API URLs work from any page directory.
$projectRoot = '/Vital_Drop';
$chatbotCss  = $projectRoot . '/assets/css/chatbot.css';
$chatbotJs   = $projectRoot . '/assets/js/chatbot.js';
$chatbotApi  = $projectRoot . '/Vital_Bot_Grok/vitaldrop-bot/backend/chat.php';
?>

<!-- ============================================================
     VITALDROP CHATBOT WIDGET — INTEGRATED
     Session-gated: only visible to logged-in users.
     ============================================================ -->

<!-- Chatbot Stylesheet (loaded once, scoped with #vitaldrop-widget) -->
<link rel="stylesheet" href="<?php echo $chatbotCss; ?>">

<!-- Chatbot Widget HTML -->
<div id="vitaldrop-widget" data-backend-url="<?php echo $chatbotApi; ?>">

  <!-- Toggle Button (bottom-right floating icon) -->
  <button id="vd-toggle-btn" aria-label="Open VitalDrop Chat">
    <span id="vd-icon-open">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
    </span>
    <span id="vd-icon-close" style="display:none;">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </span>
  </button>

  <!-- Chat Window (hidden by default, opens on click) -->
  <div id="vd-chat-window" aria-hidden="true">

    <!-- Header -->
    <div id="vd-header">
      <div id="vd-header-left">
        <div id="vd-logo-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C12 2 5 10.5 5 15a7 7 0 0 0 14 0C19 10.5 12 2 12 2z"/>
          </svg>
        </div>
        <div>
          <div id="vd-title">VitalDrop Assistant</div>
          <div id="vd-subtitle">Nurse Consultant - Online</div>
        </div>
      </div>
      <button id="vd-close-btn" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Messages -->
    <div id="vd-messages" role="log" aria-live="polite"></div>

    <!-- Quick Replies -->
    <div id="vd-quick-replies">
      <button class="vd-quick-btn" data-msg="How do I donate blood?">Donate Blood</button>
      <button class="vd-quick-btn" data-msg="How do I find blood?">Find Blood</button>
      <button class="vd-quick-btn" data-msg="Am I eligible to donate blood?">Eligibility</button>
      <button class="vd-quick-btn" data-msg="How do I find a blood bank?">Blood Banks</button>
    </div>

    <!-- Input -->
    <div id="vd-input-area">
      <input
        type="text"
        id="vd-input"
        placeholder="Ask me anything about blood donation..."
        autocomplete="off"
        maxlength="500"
      />
      <button id="vd-send-btn" aria-label="Send">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
      </button>
    </div>

  </div>
</div>
<!-- END VITALDROP CHATBOT WIDGET -->

<!-- Chatbot JavaScript (loaded after DOM, self-initializing IIFE) -->
<script src="<?php echo $chatbotJs; ?>"></script>
