<?php
require 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS blood_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    hospital_name VARCHAR(255) NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    location VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50) NOT NULL,
    units_required INT UNSIGNED NOT NULL DEFAULT 1,
    urgency ENUM('Normal', 'Urgent') DEFAULT 'Normal',
    status ENUM('Active', 'Fulfilled', 'Cancelled', 'Expired') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL
)";

try {
    $pdo->exec($sql);
    echo "Table blood_requests created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
