<?php
require_once '../includes/session.php';
require_once '../config/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, location, blood_group FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red; padding: 20px;'>User not found.</p>";
    exit;
}

$full_name = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
?>

<div class="vd-settings-container">
    
    <form id="profileSettingsForm" class="vd-form-card">
        <div class="vd-form-grid">
            <div class="vd-form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" required>
            </div>
            
            <div class="vd-form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" required>
            </div>
            
            
            <div class="vd-form-group">
                <label>Blood Group</label>
                <input type="text" name="blood_group" value="<?= htmlspecialchars($user['blood_group'] ?? '') ?>" readonly class="vd-readonly-input">
                <small style="color: #888; font-size: 11px;">Contact admin to change blood group.</small>
            </div>
        </div>

        <button type="submit" id="saveProfileBtn" class="vd-action-btn-red">
            Save Changes
        </button>
        <div id="settingsFeedback" style="margin-top: 15px; font-weight: bold; font-size: 14px;"></div>
    </form>
</div>

