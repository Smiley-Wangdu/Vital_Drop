<?php
/**
 * VitalDrop — config.php
 * ============================================================
 * HOW TO SET YOUR GROQ API KEY:
 *
 * Option A (Recommended — Environment Variable):
 *   In XAMPP's Apache httpd.conf or your .htaccess, add:
 *     SetEnv GROQ_API_KEY "gsk_..."
 *
 * Option B (Quick local setup):
 *   Replace the empty string "" below with your actual key.
 *   Get your free key at: https://console.groq.com
 *   NEVER push this file to a public repo with a real key.
 * ============================================================
 */

// --- Groq API Key ---
define("GROQ_API_KEY", getenv("GROQ_API_KEY") ?: "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");

// Option B: define("GROQ_API_KEY", "gsk_YOUR_KEY_HERE");

// --- Groq API Settings ---
define("GROQ_API_URL", "https://api.groq.com/openai/v1/chat/completions");
define("GROQ_MODEL", "llama-3.3-70b-versatile");
define("GROQ_MAX_TOKENS", 600);

// --- Database ---
define("DB_HOST", getenv("DB_HOST") ?: "localhost");
define("DB_NAME", getenv("DB_NAME") ?: "vitaldrop");
define("DB_USER", getenv("DB_USER") ?: "root");
define("DB_PASSWORD", getenv("DB_PASSWORD") ?: "");
define("DB_CHARSET", "utf8mb4");




