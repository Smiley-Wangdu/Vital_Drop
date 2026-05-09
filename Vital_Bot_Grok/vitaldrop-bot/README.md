# VitalDrop Chatbot — Groq AI Edition

A floating chatbot widget for the VitalDrop Blood Donation Management System.
Powered by **Groq AI (Llama 3 70B)** — completely free.
Personality: **Nurse Clara**, a warm and professional nurse consultant.

---

## Folder Structure

```
vitaldrop-bot/
  index.html                  Demo host page
  frontend/
    css/chatbot.css            Widget styles
    js/chatbot.js              Widget logic & API bridge
  backend/
    chat.php                  Groq API handler
    config.php                API key & DB settings  <-- EDIT THIS
    db.php                    MySQL PDO connection
  database/
    schema.sql                Run this once in phpMyAdmin
  assets/images/              Place logo.png here (optional)
  README.md
```

---

## Step-by-Step Setup (XAMPP)

### 1. Get Your FREE Groq API Key
1. Go to https://console.groq.com
2. Sign up with Google or email — no credit card needed
3. Click **API Keys** in the left menu
4. Click **Create API Key** and copy it

### 2. Copy Files to XAMPP
```
Place vitaldrop-bot/ inside:  C:\xampp\htdocs\vitaldrop-bot\
```

### 3. Add Your API Key
Open `backend/config.php` and replace the empty string:
```php
define("GROQ_API_KEY", "gsk_YOUR_KEY_HERE");
```

### 4. Set Up the Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click **Import** tab
3. Choose `database/schema.sql`
4. Click **Go**

> Note: The chatbot works even without MySQL — the DB is only used for logging.

### 5. Start XAMPP
- Start **Apache** and **MySQL** in XAMPP Control Panel

### 6. Open the Chatbot
Visit: **http://localhost/vitaldrop-bot/**

Click the red button in the bottom-right corner.

---

## How It Works

```
User types message
  → chatbot.js sends POST to backend/chat.php
  → chat.php logs query to MySQL
  → chat.php sends message + history to Groq API
  → Groq AI (Nurse Clara) generates reply
  → chat.php logs reply to MySQL
  → Reply sent back to chatbot.js
  → Message displayed in chat window
```

**Conversation memory:** The last 10 messages are sent as context
so Nurse Clara remembers what was discussed earlier in the session.

---

## Nurse Clara — AI Personality

Nurse Clara is configured via the system prompt in `chat.php`.
She will:
- Respond warmly and professionally like a nurse consultant
- Only answer blood donation and VitalDrop related questions
- Politely decline off-topic questions
- Use clear, simple, non-medical language

To change her personality, edit the `$systemPrompt` variable in `backend/chat.php`.

---

## Groq API Details

| Setting     | Value                   |
|-------------|-------------------------|
| Model       | llama3-70b-8192         |
| Max tokens  | 600 per response        |
| Temperature | 0.65 (balanced)         |
| Free limit  | 14,400 requests/day     |
| Cost        | Free                    |

---

## Embedding on Another Website

Copy the widget block from `index.html` (the `div#vitaldrop-widget` and everything inside it) to your target page, then add:

```html
<link rel="stylesheet" href="/path/to/vitaldrop-bot/frontend/css/chatbot.css">
<script src="/path/to/vitaldrop-bot/frontend/js/chatbot.js"></script>
```

Update `backendUrl` in `chatbot.js` to the correct server path to `chat.php`.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| "API key not configured" | Add your key to `backend/config.php` |
| Widget not visible | Open browser console and check for JS errors |
| CORS error | Access via `http://localhost/...` not `file://` |
| DB error in logs | Run `schema.sql` in phpMyAdmin; chat still works without DB |
| Slow response | Normal — Groq usually replies in 1-3 seconds |

---

## Customization

| What to change | Where |
|----------------|-------|
| AI personality / restrictions | `$systemPrompt` in `backend/chat.php` |
| Colors / theme | CSS variables in `frontend/css/chatbot.css` |
| Quick reply buttons | `data-msg` attributes in `index.html` |
| Welcome message | First `addBotMessage(...)` in `frontend/js/chatbot.js` |
| AI model | `GROQ_MODEL` constant in `backend/config.php` |
| Max response length | `GROQ_MAX_TOKENS` in `backend/config.php` |
