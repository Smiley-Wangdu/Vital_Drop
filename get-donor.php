<?php
/**
 * API: Get donor profile
 * Fixed: uses $pdo from db.php directly (no getDB() function exists)
 * Fixed: users table has no `phone` or `address` column — falls back gracefully
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

// Fetch user — note: `phone` and `address` are NOT in the users table per the schema.
// We select what exists and default the rest to empty string.
$stmt = $pdo->prepare(
    "SELECT id,
            CONCAT(first_name, ' ', last_name) AS full_name,
            email,
            blood_group,
            location AS address
     FROM users
     WHERE id = ?"
);
$stmt->execute([$donorId]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// phone does not exist in users table — default to empty so JS can prefill when available
$donor['phone'] = '';

// Fetch donor_status
$stmt = $pdo->prepare(
    "SELECT is_available, last_donation_date, next_eligible_date, total_donations
     FROM donor_status
     WHERE donor_id = ?"
);
$stmt->execute([$donorId]);
$status = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$status) {
    $status = [
        'is_available'       => 1,
        'last_donation_date' => null,
        'next_eligible_date' => null,
        'total_donations'    => 0,
    ];
}

echo json_encode(['success' => true, 'donor' => $donor, 'status' => $status]);