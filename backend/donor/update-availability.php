<?php
/**
 * API: Update donor availability toggle
 * Fixed: uses $pdo from db.php directly (no getDB() function exists)
 */
header('Content-Type: application/json');

require_once '../../config/db.php';    // provides $pdo
require_once '../../includes/session.php';

requireLogin();

$donorId = $_SESSION['user_id'] ?? null;
if (!$donorId) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$isAvailable = isset($data['is_available']) ? (int)(bool)$data['is_available'] : 1;

$stmt = $pdo->prepare(
    "INSERT INTO donor_status (donor_id, is_available, total_donations)
     VALUES (?, ?, 0)
     ON DUPLICATE KEY UPDATE is_available = ?"
);
$stmt->execute([$donorId, $isAvailable, $isAvailable]);

echo json_encode(['success' => true, 'is_available' => $isAvailable]);