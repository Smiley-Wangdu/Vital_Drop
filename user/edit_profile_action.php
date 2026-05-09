<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    
    // Split full name into first and last name for the DB
    $name_parts = explode(' ', $full_name, 2);
    $first_name = $name_parts[0];
    $last_name = $name_parts[1] ?? '';

    if (empty($first_name) || empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and Location are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, location = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $location, $user_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
