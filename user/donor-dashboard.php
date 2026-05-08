<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// USER INFO
$stmt = $pdo->prepare("
    SELECT 
        CONCAT(first_name, ' ', last_name) AS full_name,
        blood_group,
        location
    FROM users 
    WHERE id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// DONATIONS COUNT
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM donations 
        WHERE donor_id = ?
    ");
    $stmt->execute([$user_id]);
    $total_donations = $stmt->fetchColumn();
} catch (Exception $e) {
    $total_donations = 0;
}

// TOTAL BLOOD REQUESTS (ALL: pending + fulfilled + others)
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM blood_requests 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$total_received = $stmt->fetchColumn();

// JOINED CAMPAIGNS (FIX THIS TABLE NAME IF DIFFERENT)
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM campaign_participants 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $total_campaigns = $stmt->fetchColumn();
} catch (Exception $e) {
    $total_campaigns = 0;
}
?>

<body data-page="dashboard">

<div class="vd-dashboard-wrapper">

    <!-- HEADER (CENTERED) -->
    <header class="vd-top-header vd-dashboard-header">
        <div class="vd-page-title-wrap center-title">
            <div class="vd-page-icon">
                <i class="fa-solid fa-address-card"></i>
            </div>
            <div class="vd-page-title">User Profile</div>
        </div>
    </header>

    <main class="vd-main-content">

        <div class="vd-content">

            <!-- PROFILE SECTION (NEW LAYOUT) -->
            <div class="vd-profile-section">

                <!-- AVATAR -->
                <div class="vd-profile-avatar-large">
                    <i class="fa-solid fa-user"></i>
                </div>

                <!-- USER INFO RIGHT SIDE -->
                <div class="vd-profile-info">

                    <div class="vd-profile-name">
                        <?= htmlspecialchars($user['full_name'] ?? 'User') ?>
                    </div>

                    <div class="vd-profile-blood">
                        Blood Group: <?= htmlspecialchars($user['blood_group'] ?? 'N/A') ?>
                    </div>

                    <div class="vd-profile-location">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($user['location'] ?? 'N/A') ?>
                    </div>

                </div>

            </div>

            <!-- STATS SECTION (BELOW PROFILE) -->
            <div class="vd-stats-grid">

                <div class="vd-stat-card" onclick="loadSection('requests')">
                    <div class="vd-stat-label">
                        <i class="fa-solid fa-list"></i> My Requests
                    </div>
                    <div class="vd-stat-value"><?= $total_received ?></div>
                    <button class="vd-stat-btn">Requests History</button>
                </div>

                <div class="vd-stat-card" onclick="loadSection('donations')">
                    <div class="vd-stat-label">
                        <i class="fa-solid fa-droplet"></i> Donations
                    </div>
                    <div class="vd-stat-value"><?= $total_donations ?></div>
                    <button class="vd-stat-btn">Donations History</button>
                </div>

                <div class="vd-stat-card" onclick="loadSection('campaigns')">
                    <div class="vd-stat-label">
                        <i class="fa-solid fa-hand-holding-medical"></i> Campaigns
                    </div>
                    <div class="vd-stat-value"><?= $total_campaigns ?></div>
                    <button class="vd-stat-btn">Joined Campaigns</button>
                </div>

            </div>

            <!-- DYNAMIC CONTENT -->
            <div id="profile-dynamic-content">
                <p class="loader">Select a section to view details</p>
            </div>

        </div>

    </main>
</div>

<script src="../assets/js/donor.js"></script>
</body>