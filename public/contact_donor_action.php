<?php
session_start();
require '../config/db.php';
require '../config/send_mail.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$request_id = $_GET['request_id'] ?? null;
$donor_id = $_GET['donor_id'] ?? null;

if (!$request_id || !$donor_id) {
    die("Invalid request.");
}

// Fetch the blood request to ensure it belongs to the user
$stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $request_id, 'user_id' => $_SESSION['user_id']]);
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) {
    die("Request not found or access denied.");
}

$needed_blood = $req['blood_group'];
$requester_name = $_SESSION['user_name'] ?? 'A user';
$requester_location = $req['location'];
$requester_phone = $req['contact_number'];

// Fetch the requester's email
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$requester_user = $stmt->fetch(PDO::FETCH_ASSOC);
$requester_email = $requester_user ? $requester_user['email'] : 'N/A';

// Fetch the donor details
$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $donor_id]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor) {
    die("Donor not found.");
}

$donor_name = $donor['first_name'] . ' ' . $donor['last_name'];
$donor_email = $donor['email'];

// Send the contact email
$mail_sent = sendDonorContactMail($donor_email, $donor_name, $requester_name, $needed_blood, $requester_email, $requester_phone, $requester_location);


if ($mail_sent) {
    header("Location: compatible_donors.php?request_id=" . $request_id . "&mail=success");
} else {
    header("Location: compatible_donors.php?request_id=" . $request_id . "&mail=error");
}
exit;
