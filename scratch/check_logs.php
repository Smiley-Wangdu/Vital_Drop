<?php
require 'config/db.php';
try {
    $pdo->query("SELECT 1 FROM chat_logs LIMIT 1");
    echo "Table chat_logs exists.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
