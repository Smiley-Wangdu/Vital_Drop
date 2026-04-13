<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If not logged in, redirect to login
    if (!isset($_SESSION['user_id'])) {
        // You could store post data in session to restore after login, but for simplicity:
        $_SESSION['error_msg'] = "Please login to request blood.";
        header("Location: ../auth/login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $blood_group = $_POST['blood_group'] ?? '';
    $hospital_name = $_POST['hospital_name'] ?? '';
    $location = $_POST['location'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $units_required = $_POST['units_required'] ?? 1;
    $urgency = $_POST['urgency'] ?? 'Normal';

    // Validation
    if (empty($blood_group) || empty($hospital_name) || empty($location) || empty($contact_number)) {
        die("Please fill in all required fields.");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO blood_requests 
            (user_id, hospital_name, blood_group, location, contact_number, units_required, urgency, expires_at) 
            VALUES (:user_id, :hospital_name, :blood_group, :location, :contact_number, :units_required, :urgency, DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        
        $stmt->execute([
            'user_id' => $user_id,
            'hospital_name' => $hospital_name,
            'blood_group' => $blood_group,
            'location' => $location,
            'contact_number' => $contact_number,
            'units_required' => $units_required,
            'urgency' => $urgency
        ]);

        $request_id = $pdo->lastInsertId();

        // Redirect to a page that shows compatible donors
        header("Location: compatible_donors.php?request_id=" . $request_id);
        exit;

    } catch (PDOException $e) {
        die("Error creating request: " . $e->getMessage());
    }
} else {
    header("Location: contact.php");
    exit;
}
?>
