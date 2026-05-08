<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

function respond($msg)
{
    echo trim($msg);
    exit;
}

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    respond("unauthorized");
}

$user_id = $_SESSION['user_id'];

/* GET POST DATA */
$campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
$campaign_name = trim($_POST['campaign_name'] ?? '');
$location = trim($_POST['location'] ?? '');

/* VALIDATION */
if (!$campaign_id || !$campaign_name || !$location) {
    file_put_contents("debug_join.log", print_r($_POST, true));
    respond("required");
}

/* CHECK CAMPAIGN EXISTS */
$stmt = $pdo->prepare("SELECT id FROM campaigns WHERE id = ?");
$stmt->execute([$campaign_id]);

if (!$stmt->fetch()) {
    respond("not_found");
}

/* CHECK ALREADY JOINED */
$stmt = $pdo->prepare("
    SELECT id FROM campaign_participants
    WHERE user_id = ? AND campaign_id = ?
    LIMIT 1
");
$stmt->execute([$user_id, $campaign_id]);

if ($stmt->fetch()) {
    respond("already_joined");
}

/* INSERT JOIN */
$stmt = $pdo->prepare("
    INSERT INTO campaign_participants
    (user_id, campaign_id)
    VALUES (?, ?)
");

if ($stmt->execute([$user_id, $campaign_id])) {
    respond("success");
}

respond("error");
?>