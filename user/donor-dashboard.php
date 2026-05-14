<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// USER INFO (✅ added health_notes)
$stmt = $pdo->prepare("
    SELECT 
        CONCAT(first_name, ' ', last_name) AS full_name,
        blood_group,
        location,
        health_notes
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

// TOTAL BLOOD REQUESTS
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM blood_requests 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$total_received = $stmt->fetchColumn();

// JOINED CAMPAIGNS
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

    <!-- HEADER -->
    <header class="vd-top-header vd-dashboard-header">
        <div class="vd-page-title-wrap">
            <div class="vd-page-icon">
                <i class="fa-solid fa-address-card"></i>
            </div>
            <div class="vd-page-title">User Profile</div>
        </div>
    </header>

    <main class="vd-main-content">

        <div class="vd-content">

            <!-- PROFILE HEADER CARD -->
            <div class="vd-profile-header-card">
                <div class="vd-profile-avatar-white">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="vd-profile-details">
                    <h2 class="vd-profile-name-large">
                        <?= htmlspecialchars($user['full_name'] ?? 'User') ?>
                    </h2>

                    <!-- ✅ UPDATED INFO ROW -->
                    <div class="vd-profile-info-row">
                        <span>
                            <i class="fa-solid fa-droplet" style="color: #af0000;"></i> 
                            Blood Group: 
                            <strong><?= htmlspecialchars($user['blood_group'] ?? 'O+') ?></strong>
                        </span>

                        <span>
                            <i class="fa-solid fa-location-dot" style="color: #af0000;"></i> 
                            <?= htmlspecialchars($user['location'] ?? 'Kathmandu') ?>
                        </span>

                        <span>
                            <i class="fa-solid fa-notes-medical" style="color: #af0000;"></i> 
                            <?= !empty($user['health_notes']) 
                                ? htmlspecialchars($user['health_notes']) 
                                : 'No health notes added' ?>
                        </span>
                    </div>

                    <button class="vd-edit-profile-btn" onclick="openSettingsModal()">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Profile Settings
                    </button>
                </div>

                <!-- STATS -->
                <div class="vd-stats-row">
                    <div class="vd-stat-mini-card">
                        <div class="vd-stat-num"><?= $total_received ?></div>
                        <div class="vd-stat-label-mini">
                            <i class="fa-solid fa-list-ul"></i> REQUESTS
                        </div>
                        <button class="vd-stat-action-btn" onclick="loadSection('requests')">
                            View History
                        </button>
                    </div>

                    <div class="vd-stat-mini-card">
                        <div class="vd-stat-num"><?= $total_donations ?></div>
                        <div class="vd-stat-label-mini">
                            <i class="fa-solid fa-hand-holding-heart"></i> DONATIONS
                        </div>
                        <button class="vd-stat-action-btn" onclick="loadSection('donations')">
                            View History
                        </button>
                    </div>

                    <div class="vd-stat-mini-card">
                        <div class="vd-stat-num"><?= $total_campaigns ?></div>
                        <div class="vd-stat-label-mini">
                            <i class="fa-solid fa-house-medical"></i> CAMPAIGNS
                        </div>
                        <button class="vd-stat-action-btn" onclick="loadSection('campaigns')">
                            View Joined
                        </button>
                    </div>
                </div>
            </div>

            <!-- LOWER GRID -->
            <div class="vd-dashboard-grid">
                
                <!-- LEFT -->
                <div class="vd-grid-left">
                    
                    <!-- IMPACT JOURNEY -->
                    <div class="vd-journey-card">
                        <h3 class="vd-section-title">
                            <i class="fa-solid fa-chart-line"></i> Your Impact Journey
                        </h3>

                        <div class="vd-journey-meta">
                            <span class="vd-level-text">
                                Level: <strong>Bronze Donor</strong>
                            </span>
                            <span class="vd-progress-text">
                                <strong><?= $total_donations ?> / 3</strong> Donations to Silver
                            </span>
                        </div>

                        <div class="vd-progress-bar-container">
                            <div class="vd-progress-bar"
                                 style="width: <?= min(($total_donations / 3) * 100, 100) ?>%;">
                            </div>
                        </div>

                        <div class="vd-badges-section">
                            <h4 class="vd-badges-title">
                                <i class="fa-solid fa-medal"></i> Earned Badges
                            </h4>
                            <div class="vd-badges-placeholder">
                                <p>No badges earned yet. Complete more donations to unlock!</p>
                            </div>
                        </div>
                    </div>

                    <!-- DYNAMIC CONTENT -->
                    <div id="profile-dynamic-content" class="vd-dynamic-area"></div>
                </div>

                <!-- RIGHT -->
                <div class="vd-grid-right">

                    <!-- QUICK ACTIONS -->
                    <div class="vd-sidebar-widget-card vd-quick-actions-card">
                        <h3 class="vd-section-title">
                            <i class="fa-solid fa-bolt"></i> Quick Actions
                        </h3>

                        <div class="vd-action-buttons">
                            <button class="vd-action-btn-red"
                                    onclick="document.getElementById('sidebar-donate-blood').click()">
                                <i class="fa-solid fa-calendar-plus"></i> Schedule Donation
                            </button>

                            <button class="vd-action-btn-red"
                                    onclick="document.getElementById('sidebar-request-blood').click()">
                                <i class="fa-solid fa-truck-medical"></i> Emergency Request
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </main>
</div>

<!-- SETTINGS MODAL -->
<div id="vd-settings-modal" class="vd-modal-overlay" style="display: none;">
    <div class="vd-modal-box">
        <div class="vd-modal-header">
            <h3><i class="fa-solid fa-user-gear"></i> Profile Settings</h3>
            <button class="vd-modal-close" onclick="closeSettingsModal()">&times;</button>
        </div>
        <div id="vd-modal-body" class="vd-modal-body">
            <p id="rb-loading">Loading settings...</p>
        </div>
    </div>
</div>

<script src="../assets/js/donor.js"></script>
</body>