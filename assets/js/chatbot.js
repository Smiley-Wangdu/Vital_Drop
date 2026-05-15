/**
 * VitalDrop Chatbot Widget — chatbot.js
 * ============================================================
 * INTEGRATED INTO MAIN PROJECT from Vital_Bot_Grok
 * 
 * All AI responses come from Groq API via the chatbot backend.
 * No hardcoded FAQ answers — fully dynamic AI responses.
 * 
 * The backendUrl is dynamically set using the data-backend-url
 * attribute on the #vitaldrop-widget element, injected by the
 * PHP include. This avoids hardcoded path issues across
 * different page directories (public/, user/, admin/).
 * ============================================================
 */

(function () {
  "use strict";

  /* ============================================================
     CONFIG — backend URL is set dynamically from PHP
     ============================================================ */

  // Read the backend URL from the widget's data attribute (set by PHP)
  const widgetEl = document.getElementById("vitaldrop-widget");
  if (!widgetEl) return; // Widget not present on this page (not logged in)

  const CONFIG = {
    backendUrl:      widgetEl.getAttribute("data-backend-url") || "/Vital_Drop/Vital_Bot_Grok/vitaldrop-bot/backend/chat.php",
    sessionKey:      "vd_history",
    maxHistory:      20,       // messages kept in session
    historyContext:  10,       // messages sent to AI for context
    typingDelayMin:  600,      // ms min before response shows
    typingDelayMax:  1200,     // ms max (simulates reading)
  };

  /* ============================================================
     DOM ELEMENTS
     ============================================================ */
  const toggleBtn  = document.getElementById("vd-toggle-btn");
  const closeBtn   = document.getElementById("vd-close-btn");
  const chatWindow = document.getElementById("vd-chat-window");
  const messagesEl = document.getElementById("vd-messages");
  const inputEl    = document.getElementById("vd-input");
  const sendBtn    = document.getElementById("vd-send-btn");
  const iconOpen   = document.getElementById("vd-icon-open");
  const iconClose  = document.getElementById("vd-icon-close");
  const quickBtns  = document.querySelectorAll(".vd-quick-btn");

  /* ============================================================
     STATE
     ============================================================ */
  let isOpen       = false;
  let isBusy       = false;
  let chatHistory  = [];   // { role: "user"|"bot", text: "..." }

  /* ============================================================
     INIT
     ============================================================ */
  function init() {
    // Verify all required DOM elements exist
    if (!toggleBtn || !closeBtn || !chatWindow || !messagesEl || !inputEl || !sendBtn) {
      console.warn("[VitalDrop Chatbot] Missing DOM elements. Widget may not work correctly.");
      return;
    }

    loadHistory();

    if (chatHistory.length > 0) {
      chatHistory.forEach(m => renderBubble(m.role, m.text, false));
    } else {
      addBotMessage(
        "Hello! I am Nurse Clara, your VitalDrop assistant. I am here to guide you through everything related to blood donation — from eligibility and the donation process to finding blood banks and making requests. How can I assist you today?"
      );
    }

    toggleBtn.addEventListener("click", toggleChat);
    closeBtn.addEventListener("click", closeChat);
    sendBtn.addEventListener("click", handleSend);
    inputEl.addEventListener("keydown", e => {
      if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); handleSend(); }
    });
    quickBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        const msg = btn.getAttribute("data-msg");
        if (msg && !isBusy) { inputEl.value = msg; handleSend(); }
      });
    });
  }

  /* ============================================================
     TOGGLE / OPEN / CLOSE
     ============================================================ */
  function toggleChat() { isOpen ? closeChat() : openChat(); }

  function openChat() {
    isOpen = true;
    chatWindow.classList.add("vd-open");
    chatWindow.setAttribute("aria-hidden", "false");
    iconOpen.style.display  = "none";
    iconClose.style.display = "block";
    scrollBottom();
    setTimeout(() => inputEl.focus(), 280);
  }

  function closeChat() {
    isOpen = false;
    chatWindow.classList.remove("vd-open");
    chatWindow.setAttribute("aria-hidden", "true");
    iconOpen.style.display  = "block";
    iconClose.style.display = "none";
  }

  /* ============================================================
     SEND HANDLER
     ============================================================ */
  async function handleSend() {
    if (isBusy) return;

    const text = inputEl.value.trim();
    if (!text) { pulseInput(); return; }

    inputEl.value = "";
    addUserMessage(text);
    setBusy(true);

    const typingEl = showTyping();

    // Enforce a minimum display time so typing indicator doesn't flash
    const minWait  = new Promise(r => setTimeout(r, CONFIG.typingDelayMin));

    try {
      const [reply] = await Promise.all([fetchReply(text), minWait]);
      removeEl(typingEl);
      addBotMessage(reply);
    } catch (err) {
      removeEl(typingEl);
      addBotMessage(
        "I am sorry, I was unable to process your request right now. Please check your connection and try again."
      );
    } finally {
      setBusy(false);
    }
  }

  /* ============================================================
     FETCH REPLY FROM BACKEND (Groq)
     ============================================================ */
  async function fetchReply(userText) {
    // Send recent history as context so the AI remembers the conversation
    const context = chatHistory
      .slice(-(CONFIG.historyContext))
      .map(m => ({ role: m.role === "user" ? "user" : "assistant", content: m.text }));

    const res = await fetch(CONFIG.backendUrl, {
      method:  "POST",
      headers: { "Content-Type": "application/json" },
      body:    JSON.stringify({ message: userText, history: context })
    });

    if (!res.ok) throw new Error("HTTP " + res.status);

    const data = await res.json();

    if (data.error)  throw new Error(data.error);
    if (data.reply)  return data.reply;

    throw new Error("Empty reply from server.");
  }

  /* ============================================================
     MESSAGE HELPERS
     ============================================================ */
  function addUserMessage(text) {
    chatHistory.push({ role: "user", text });
    saveHistory();
    renderBubble("user", text, true);
  }

  function addBotMessage(text) {
    chatHistory.push({ role: "bot", text });
    saveHistory();
    renderBubble("bot", text, true);
  }

  /* ============================================================
     RENDER
     ============================================================ */
  function renderBubble(role, text, animate) {
    const row = document.createElement("div");
    row.className = "vd-msg " + role;
    if (!animate) row.style.animation = "none";

    if (role === "bot") {
      const av = document.createElement("div");
      av.className = "vd-avatar";
      av.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C12 2 5 10.5 5 15a7 7 0 0 0 14 0C19 10.5 12 2 12 2z"/>
      </svg>`;
      row.appendChild(av);
    }

    const col = document.createElement("div");
    col.className = "vd-bubble-col";

    const bubble = document.createElement("div");
    bubble.className = "vd-bubble";
    bubble.innerHTML = formatText(text);

    const ts = document.createElement("div");
    ts.className = "vd-ts";
    ts.textContent = nowTime();

    col.appendChild(bubble);
    col.appendChild(ts);
    row.appendChild(col);

    messagesEl.appendChild(row);
    scrollBottom();
  }

  function showTyping() {
    const row = document.createElement("div");
    row.className = "vd-typing-row";

    const av = document.createElement("div");
    av.className = "vd-avatar";
    av.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 2C12 2 5 10.5 5 15a7 7 0 0 0 14 0C19 10.5 12 2 12 2z"/>
    </svg>`;

    const bubble = document.createElement("div");
    bubble.className = "vd-typing-bubble";
    bubble.innerHTML = `<span class="vd-dot"></span><span class="vd-dot"></span><span class="vd-dot"></span>`;

    row.appendChild(av);
    row.appendChild(bubble);
    messagesEl.appendChild(row);
    scrollBottom();
    return row;
  }

  /* ============================================================
     UTILITIES
     ============================================================ */
  function formatText(text) {
    // Escape HTML, then convert newlines to <br>
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/\n/g, "<br>");
  }

  function scrollBottom() {
    requestAnimationFrame(() => { messagesEl.scrollTop = messagesEl.scrollHeight; });
  }

  function removeEl(el) { if (el && el.parentNode) el.parentNode.removeChild(el); }

  function setBusy(val) {
    isBusy            = val;
    sendBtn.disabled  = val;
    inputEl.disabled  = val;
  }

  function pulseInput() {
    inputEl.style.borderColor = "var(--vd-primary)";
    inputEl.focus();
    setTimeout(() => { inputEl.style.borderColor = ""; }, 1000);
  }

  function nowTime() {
    return new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  }

  function saveHistory() {
    try {
      sessionStorage.setItem(CONFIG.sessionKey, JSON.stringify(chatHistory.slice(-CONFIG.maxHistory)));
    } catch (_) {}
  }

  function loadHistory() {
    try {
      const s = sessionStorage.getItem(CONFIG.sessionKey);
      if (s) chatHistory = JSON.parse(s);
    } catch (_) { chatHistory = []; }
  }

  /* ============================================================
     BOOT
     ============================================================ */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

})();
