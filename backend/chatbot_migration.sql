-- ============================================================
-- VitalDrop Chatbot — chat_logs Table Migration
-- ============================================================
-- Run this ONCE in phpMyAdmin before using the chatbot.
-- The vitaldrop database must already exist (it does if the
-- main project is already running).
--
-- HOW TO RUN:
--   1. Open phpMyAdmin → http://localhost/phpmyadmin
--   2. Select the "vitaldrop" database from the left sidebar
--   3. Click the "SQL" tab
--   4. Paste this entire file and click "Go"
-- ============================================================

USE `vitaldrop`;

-- Creates the chat_logs table only if it doesn't already exist.
-- Safe to run multiple times.
CREATE TABLE IF NOT EXISTS `chat_logs` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_message` TEXT          NOT NULL                    COMMENT 'The message sent by the user',
    `bot_reply`    TEXT                   DEFAULT NULL       COMMENT 'The AI reply from Nurse Clara',
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores all chatbot conversation turns for analytics';
