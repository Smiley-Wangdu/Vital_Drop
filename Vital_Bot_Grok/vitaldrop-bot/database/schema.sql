-- ============================================================
-- VitalDrop Chatbot — Database Schema
-- Run this once in phpMyAdmin before using the chatbot.
-- ============================================================

CREATE DATABASE IF NOT EXISTS vitaldrop
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE vitaldrop;

-- Stores every user query and the AI's reply
CREATE TABLE IF NOT EXISTS chat_logs (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_message  TEXT          NOT NULL,
    bot_reply     TEXT                   DEFAULT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY   (id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
