<?php
/**
 * API: Submit donation form
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
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body']);
    exit;
}

// ── Required field validation ──────────────────────────────────────────────
$required = ['blood_group', 'phone', 'address', 'hospital_name'];
foreach ($required as $field) {
    if (empty(trim($data[$field] ?? ''))) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required."]);
        exit;
    }
}

// ── Phone: exactly 10 digits ───────────────────────────────────────────────
if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
    echo json_encode(['success' => false, 'message' => 'Phone number must be exactly 10 digits.']);
    exit;
}

// ── Sanitise inputs ────────────────────────────────────────────────────────
$bloodGroup   = trim($data['blood_group']);
$phone        = trim($data['phone']);
$address      = trim($data['address']);
$hospitalName = trim($data['hospital_name']);
$location     = trim($data['location']  ?? '');
$notes        = trim($data['notes']     ?? '');
$todayStr     = date('Y-m-d');

try {
    // ── PB4: 3-month eligibility check ────────────────────────────────────
    $stmt = $pdo->prepare(
        "SELECT last_donation_date FROM donor_status WHERE donor_id = ?"
    );
    $stmt->execute([$donorId]);
    $status = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($status && $status['last_donation_date']) {
        $lastDate    = new DateTime($status['last_donation_date']);
        $today       = new DateTime();
        $interval    = $lastDate->diff($today);
        $monthsPassed = ($interval->y * 12) + $interval->m;

        if ($monthsPassed < 3) {
            $nextEligible = (clone $lastDate)->modify('+3 months');
            echo json_encode([
                'success'      => false,
                'blocked'      => true,
                'message'      => 'You must wait 3 months between donations.',
                'next_eligible' => $nextEligible->format('Y-m-d'),
            ]);
            exit;
        }
    }

    // ── Save donation & update status (single transaction) ────────────────
    $pdo->beginTransaction();

    // PB1: Insert into donations
    $stmt = $pdo->prepare(
        "INSERT INTO donations
            (donor_id, donation_date, blood_group, hospital_name, location, phone, address, notes, status)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([
        $donorId, $todayStr, $bloodGroup, $hospitalName,
        $location, $phone, $address, $notes,
    ]);

    // Upsert donor_status: increment total_donations, update dates
    $nextEligibleStr = date('Y-m-d', strtotime('+3 months'));
    $stmt = $pdo->prepare(
        "INSERT INTO donor_status
            (donor_id, last_donation_date, next_eligible_date, total_donations)
         VALUES (?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            last_donation_date = VALUES(last_donation_date),
            next_eligible_date = VALUES(next_eligible_date),
            total_donations    = total_donations + 1"
    );
    $stmt->execute([$donorId, $todayStr, $nextEligibleStr]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Donation submitted successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}