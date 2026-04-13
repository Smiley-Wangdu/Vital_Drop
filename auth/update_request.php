<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($request_id && $action) {
        $status = '';
        if ($action === 'fulfill') {
            $status = 'Fulfilled';
        } elseif ($action === 'cancel') {
            $status = 'Cancelled';
        }

        if ($status) {
            $stmt = $pdo->prepare("UPDATE blood_requests SET status = :status WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                'status' => $status,
                'id' => $request_id,
                'user_id' => $_SESSION['user_id']
            ]);
        }
    }
}

header("Location: dashboard.php");
exit;
?>
