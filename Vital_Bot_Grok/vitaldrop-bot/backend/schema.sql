-- ═══════════════════════════════════════════════
--  VitalDrop Bot — Database Schema
--  Run this script once to set up the database.
-- ═══════════════════════════════════════════════

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS vitaldrop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vitaldrop;

-- ── Chat Logs Table ──────────────────────────────
-- Stores every user query and bot response with timestamp
CREATE TABLE IF NOT EXISTS chat_logs (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_query   TEXT            NOT NULL,
    bot_response TEXT            NOT NULL,
    session_id   VARCHAR(64)     DEFAULT NULL COMMENT 'Optional: frontend session identifier',
    ip_address   VARCHAR(45)     DEFAULT NULL COMMENT 'Optional: for analytics',
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Optional: FAQ Cache Table ────────────────────
-- Pre-populate with common Q&A pairs for fast local lookup
CREATE TABLE IF NOT EXISTS faq_responses (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    keywords   TEXT         NOT NULL COMMENT 'Comma-separated trigger keywords',
    question   VARCHAR(500) NOT NULL,
    answer     TEXT         NOT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Seed FAQ Table ───────────────────────────────
INSERT INTO faq_responses (keywords, question, answer) VALUES
('what is vitaldrop, about vitaldrop',
 'What is VitalDrop?',
 'VitalDrop is a Blood Donation Management System that connects blood donors with recipients. It helps find blood banks, check eligibility, request blood, and manage the entire donation process.'),

('donate blood, how to donate, donation process',
 'How do I donate blood?',
 'Register on VitalDrop, verify your eligibility, schedule an appointment at a nearby blood bank. The donation itself takes about 10 minutes; the full visit is 45-60 minutes.'),

('eligibility, can i donate, requirements',
 'Am I eligible to donate blood?',
 'General criteria: Age 18-65, weight over 50 kg, no major illness, no tattoo/piercing in last 6 months, not pregnant, no donation in last 56 days.'),

('blood bank, find blood bank, nearest blood bank',
 'How do I find a blood bank?',
 'Use the "Find Blood Banks" feature on the VitalDrop dashboard. Search by city or pincode and view operating hours and directions.'),

('blood type, blood group',
 'What are the blood types?',
 'Common types: A+, A-, B+, B-, AB+, AB-, O+, O-. O- is the universal donor. AB+ is the universal recipient. VitalDrop supports filtering by blood type.');
