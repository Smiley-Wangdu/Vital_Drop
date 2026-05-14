<?php
session_start();
require '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->execute(['user_id' => $user_id]);
    $unread_count = $stmt->fetchColumn();

    echo json_encode(['unread_count' => (int)$unread_count]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
