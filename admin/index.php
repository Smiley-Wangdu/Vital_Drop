<?php
session_start();
require '../config/db.php';

// Check admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalDonors = $pdo->query("SELECT COUNT(*) FROM users WHERE is_donor = 1")->fetchColumn();
$totalReceivers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_donor = 0")->fetchColumn();
$totalCampaigns = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
$totalRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'pending'")->fetchColumn();

// Blood group distribution
$bgStmt = $pdo->query("SELECT blood_group, COUNT(*) as count FROM users GROUP BY blood_group ORDER BY count DESC");
$bloodGroups = $bgStmt->fetchAll(PDO::FETCH_ASSOC);

// Recent users
$recentUsers = $pdo->query("SELECT id, name, email, blood_group, location, role, is_donor, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Vital Drop</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1 class="page-title">Dashboard</h1>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><iconify-icon icon="mdi:account-group"></iconify-icon></div>
                    <div class="stat-info">
                        <span class="stat-label">Total Users</span>
                        <span class="stat-value"><?php echo $totalUsers; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><iconify-icon icon="mdi:water"></iconify-icon></div>
                    <div class="stat-info">
                        <span class="stat-label">Total Donors</span>
                        <span class="stat-value"><?php echo $totalDonors; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><iconify-icon icon="mdi:heart-outline"></iconify-icon></div>
                    <div class="stat-info">
                        <span class="stat-label">Total Receivers</span>
                        <span class="stat-value"><?php echo $totalReceivers; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><iconify-icon icon="mdi:bullhorn"></iconify-icon></div>
                    <div class="stat-info">
                        <span class="stat-label">Campaigns</span>
                        <span class="stat-value"><?php echo $totalCampaigns; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><iconify-icon icon="mdi:clipboard-text"></iconify-icon></div>
                    <div class="stat-info">
                        <span class="stat-label">Total Requests</span>
                        <span class="stat-value"><?php echo $totalRequests; ?></span>
                    </div>
                </div>
                <div class="stat-card highlight">
                    <div class="stat-icon"><iconify-icon icon="mdi:timer-sand"></iconify-icon></div>
                    <div class="stat-info">
                        <span class="stat-label">Pending Requests</span>
                        <span class="stat-value"><?php echo $pendingRequests; ?></span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <h3>Donors vs Receivers</h3>
                    <div class="chart-container">
                        <canvas id="donorPieChart" width="280" height="280"></canvas>
                    </div>
                    <div class="chart-legend">
                        <span class="legend-item"><span class="legend-dot donors"></span> Donors: <?php echo $totalDonors; ?></span>
                        <span class="legend-item"><span class="legend-dot receivers"></span> Receivers: <?php echo $totalReceivers; ?></span>
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Blood Group Distribution</h3>
                    <div class="blood-bars">
                        <?php 
                        $maxCount = !empty($bloodGroups) ? max(array_column($bloodGroups, 'count')) : 1;
                        foreach ($bloodGroups as $bg): 
                            $pct = ($bg['count'] / $maxCount) * 100;
                        ?>
                        <div class="bar-row">
                            <span class="bar-label"><?php echo htmlspecialchars($bg['blood_group']); ?></span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                            <span class="bar-count"><?php echo $bg['count']; ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($bloodGroups)): ?>
                            <p class="no-data">No users registered yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Users Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Recent Users</h3>
                    <a href="users.php" class="view-all-btn">View All →</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Blood Group</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Donor</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge bg-badge"><?php echo htmlspecialchars($user['blood_group']); ?></span></td>
                            <td><?php echo htmlspecialchars($user['location']); ?></td>
                            <td><span class="badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo $user['is_donor'] ? '<iconify-icon icon="mdi:check-circle" style="color: green;"></iconify-icon>' : '<iconify-icon icon="mdi:close-circle" style="color: red;"></iconify-icon>'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentUsers)): ?>
                        <tr><td colspan="7" class="no-data">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        // Pie chart data
        drawPieChart('donorPieChart', <?php echo $totalDonors; ?>, <?php echo $totalReceivers; ?>);
    </script>
</body>
</html>
