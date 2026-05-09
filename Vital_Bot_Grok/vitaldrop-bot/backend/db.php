<?php
/**
 * VitalDrop — db.php
 * PDO singleton connection. Returns null if DB is unavailable
 * so the chatbot still works without a database.
 */

require_once __DIR__ . "/config.php";

function getDB(): ?PDO {
    static $pdo = null;
    static $failed = false;

    if ($failed) return null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        DB_HOST, DB_NAME, DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        $failed = true;
        error_log("[VitalDrop] DB connection failed: " . $e->getMessage());
        return null;
    }

    return $pdo;
}
