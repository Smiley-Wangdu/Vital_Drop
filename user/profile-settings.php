<?php
require_once '../includes/session.php';
require_once '../config/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT first_name, last_name, location, blood_group, health_notes 
    FROM users 
    WHERE id = ?
");
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p style='color:red;'>User not found</p>";
    exit;
}

$full_name = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
?>

<div class="vd-settings-container">

<form id="profileSettingsForm" class="vd-form-card">

    <div class="vd-form-grid">

        <div class="vd-form-group">
            <label>Full Name</label>
            <input type="text" name="full_name"
                   value="<?= htmlspecialchars($full_name) ?>" required>
        </div>

        <div class="vd-form-group">
            <label>Location</label>
            <input type="text" name="location"
                   value="<?= htmlspecialchars($user['location'] ?? '') ?>" required>
        </div>

        <div class="vd-form-group">
            <label>Blood Group</label>
            <input type="text"
                   value="<?= htmlspecialchars($user['blood_group'] ?? '') ?>"
                   readonly>
        </div>

        <div class="vd-form-group">
            <label>Health Notes</label>
            <input type="text" name="health_notes"
                   value="<?= htmlspecialchars($user['health_notes'] ?? '') ?>">
        </div>

    </div>

    <button type="submit" id="saveProfileBtn" class="vd-action-btn-red">Save Changes</button>
    <div id="settingsFeedback"></div>

</form>

</div>