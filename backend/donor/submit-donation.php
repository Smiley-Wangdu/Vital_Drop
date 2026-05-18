<?php
header('Content-Type: application/json');

require_once '../../config/db.php';
require_once '../../includes/session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$donorId = $_SESSION['user_id'];

/* CHECK DONATION ELIGIBILITY */
$stmt = $pdo->prepare("
    SELECT next_eligible_date
    FROM donor_status
    WHERE donor_id = ?
    LIMIT 1
");

$stmt->execute([$donorId]);
$status = $stmt->fetch(PDO::FETCH_ASSOC);

if ($status && !empty($status['next_eligible_date'])) {

    $today = date('Y-m-d');

    if ($status['next_eligible_date'] > $today) {
        echo json_encode([
            "success" => false,
            "message" => "You are not eligible yet. Next eligible date: " . $status['next_eligible_date']
        ]);
        exit;
    }
}

/* SAFE JSON PARSE */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON",
        "raw" => $raw
    ]);
    exit;
}

/* VALIDATION */
$bloodGroup = trim($data['blood_group'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? ''); // optional now
$hospitalName = trim($data['hospital_name'] ?? '');
$location = trim($data['location'] ?? '');
$notes = trim($data['notes'] ?? '');

/* REQUIRED FIELDS FIXED */
if ($bloodGroup === '' || $phone === '' || $hospitalName === '' || $location === '') {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

/* BLOOD GROUP LOCK: verify submitted group matches registered profile */
$bgStmt = $pdo->prepare("SELECT blood_group FROM users WHERE id = ? LIMIT 1");
$bgStmt->execute([$donorId]);
$registeredUser = $bgStmt->fetch(PDO::FETCH_ASSOC);

if (!$registeredUser || $bloodGroup !== $registeredUser['blood_group']) {
    echo json_encode([
        "success" => false,
        "message" => "Blood group mismatch. You can only donate your registered blood group ("
            . htmlspecialchars($registeredUser['blood_group'] ?? 'unknown') . ")."
    ]);
    exit;
}

/* PHONE CLEANUP */
$phone = str_replace([' ', '+977'], '', $phone);

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode([
        "success" => false,
        "message" => "Phone must be 10 digits"
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    /* INSERT DONATION */
    $stmt = $pdo->prepare("
        INSERT INTO donations
        (donor_id, donation_date, blood_group, hospital_name, location, phone, address, notes, status)
        VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $donorId,
        $bloodGroup,
        $hospitalName,
        $location,
        $phone,
        $address,
        $notes
    ]);

    /* UPDATE DONOR STATUS */
    $stmt = $pdo->prepare("
        INSERT INTO donor_status
        (donor_id, last_donation_date, next_eligible_date, total_donations)
        VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 1)
        ON DUPLICATE KEY UPDATE
            last_donation_date = CURDATE(),
            next_eligible_date = DATE_ADD(CURDATE(), INTERVAL 90 DAY),
            total_donations = total_donations + 1
    ");

    $stmt->execute([$donorId]);

    $pdo->commit();

    // Send thank you notification to the donor
    require_once '../../includes/notification_helper.php';
    $quotes = [
        "The gift of blood is the gift of life.",
        "Your droplets of blood may create an ocean of happiness.",
        "A life may depend on a gesture from you, a bottle of blood.",
        "Heroes come in all types and sizes.",
        "To give blood you need neither extra strength nor extra food, and you will save a life."
    ];
    $quote = $quotes[array_rand($quotes)];
    $message = "Thank you for scheduling your donation at {$hospitalName}! " . $quote;
    create_notification($pdo, $donorId, $message, 'donation_thanks');

    // If this was a response to a specific request, notify the receiver
    $requestId = isset($data['request_id']) && is_numeric($data['request_id']) ? intval($data['request_id']) : null;
    
    if ($requestId) {
        $reqStmt = $pdo->prepare("SELECT user_id, blood_group, hospital_name FROM blood_requests WHERE id = ?");
        $reqStmt->execute([$requestId]);
        $req = $reqStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($req) {
            $donorNameStmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE id = ?");
            $donorNameStmt->execute([$donorId]);
            $donorFullName = $donorNameStmt->fetchColumn();
            
            $receiverMsg = "Donor {$donorFullName} has accepted and scheduled a donation for your {$req['blood_group']} blood request at {$req['hospital_name']}.";
            create_notification($pdo, $req['user_id'], $receiverMsg, 'donor_response', $donorId);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Donation saved successfully"
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        "success" => false,
        "message" => "DB Error",
        "error" => $e->getMessage()
    ]);
}