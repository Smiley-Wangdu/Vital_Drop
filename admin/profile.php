<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$msgType = "";

/* ---------------- ADMIN PROFILE ---------------- */
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found");
}

/* ---------------- UPDATE PROFILE ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $location = trim($_POST['location']);
    $age = intval($_POST['age']);

    $sql = "UPDATE admins 
            SET name=:name, email=:email, location=:location, age=:age";

    $params = [
        'name' => $name,
        'email' => $email,
        'location' => $location,
        'age' => $age,
        'id' => $_SESSION['user_id']
    ];

    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $sql .= ", password=:password";
            $params['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        } else {
            $message = "Passwords do not match!";
            $msgType = "error";
        }
    }

    if ($msgType !== "error") {
        $sql .= " WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $message = "Profile updated successfully";
        $msgType = "success";

        // refresh admin
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id=:id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* ---------------- FIXED STATS ---------------- */
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$totalDonors = $pdo->query("SELECT COUNT(*) FROM users WHERE blood_group IS NOT NULL")->fetchColumn();

$totalCampaigns = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();

/* FIXED STATUS VALUES */
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Active'")->fetchColumn();
$fulfilledRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Fulfilled'")->fetchColumn();
$totalRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();

/* ---------------- FIXED USERS (first_name + last_name) ---------------- */
$recentUsers = $pdo->query("
    SELECT 
        first_name,
        last_name,
        email,
        created_at
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* ---------------- FIXED REQUESTS ---------------- */
$recentRequests = $pdo->query("
    SELECT 
        br.blood_group,
        br.location,
        br.urgency,
        br.status,
        br.created_at,
        u.first_name,
        u.last_name
    FROM blood_requests br
    LEFT JOIN users u ON br.user_id = u.id
    ORDER BY br.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* ---------------- ACCOUNT AGE ---------------- */
$joinDate = new DateTime($admin['created_at']);
$now = new DateTime();
$accountAge = $joinDate->diff($now);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile — Vital Drop</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1 class="page-title">Admin Profile</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="profile-page-grid">
                <!-- LEFT COLUMN: Profile Info & Edit -->
                <div class="profile-left-col">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar-lg"><iconify-icon icon="mdi:account" style="font-size:inherit;"></iconify-icon></div>
                            <div>
                                <h2><?php echo htmlspecialchars($admin['name']); ?></h2>
                                <p class="profile-role"><?php echo ucfirst($admin['role']); ?> · <?php echo htmlspecialchars($admin['blood_group']); ?></p>
                                <p class="profile-meta"><iconify-icon icon="mdi:map-marker" style="vertical-align:text-bottom;"></iconify-icon> <?php echo htmlspecialchars($admin['location']); ?> · Joined <?php echo date('M Y', strtotime($admin['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="profile-edit-card">
                        <h3>Edit Profile</h3>
                        <form method="POST" class="profile-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" value="<?php echo $admin['age']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" value="<?php echo htmlspecialchars($admin['location']); ?>" required>
                                </div>
                            </div>

                            <h4 class="section-divider">Change Password (optional)</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" placeholder="Leave blank to keep current">
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input type="password" name="confirm_password" placeholder="Confirm new password">
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">Update Profile</button>
                        </form>
                    </div>

                    <!-- System Information -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:server" class="widget-icon"></iconify-icon>
                            <h3>System Information</h3>
                        </div>
                        <div class="system-info-list">
                            <div class="sys-info-row">
                                <span class="sys-info-label">Platform</span>
                                <span class="sys-info-value">VitalDrop v1.0</span>
                            </div>
                            <div class="sys-info-row">
                                <span class="sys-info-label">PHP Version</span>
                                <span class="sys-info-value"><?php echo phpversion(); ?></span>
                            </div>
                            <div class="sys-info-row">
                                <span class="sys-info-label">Server</span>
                                <span class="sys-info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></span>
                            </div>
                            <div class="sys-info-row">
                                <span class="sys-info-label">Database</span>
                                <span class="sys-info-value">MySQL <?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                            </div>
                            <div class="sys-info-row">
                                <span class="sys-info-label">Session ID</span>
                                <span class="sys-info-value" style="font-family: monospace; font-size: 11px;"><?php echo substr(session_id(), 0, 16); ?>…</span>
                            </div>
                            <div class="sys-info-row">
                                <span class="sys-info-label">Server Time</span>
                                <span class="sys-info-value"><?php echo date('M d, Y — H:i'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Blood Group Distribution -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:water" class="widget-icon"></iconify-icon>
                            <h3>Blood Group Distribution</h3>
                        </div>
                        <?php
                        $bgStmt = $pdo->query("SELECT blood_group, COUNT(*) as count FROM users GROUP BY blood_group ORDER BY count DESC");
                        $bloodGroups = $bgStmt->fetchAll(PDO::FETCH_ASSOC);
                        $maxBG = !empty($bloodGroups) ? max(array_column($bloodGroups, 'count')) : 1;
                        ?>
                        <div class="widget-blood-bars">
                            <?php if (!empty($bloodGroups)): ?>
                                <?php foreach ($bloodGroups as $bg):
                                    $pct = ($bg['count'] / $maxBG) * 100;
                                ?>
                                <div class="widget-bar-row">
                                    <span class="widget-bar-label"><?php echo htmlspecialchars($bg['blood_group']); ?></span>
                                    <div class="widget-bar-track">
                                        <div class="widget-bar-fill" style="width: <?php echo $pct; ?>%"></div>
                                    </div>
                                    <span class="widget-bar-count"><?php echo $bg['count']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="widget-no-data">No data available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Blood Requests -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:clipboard-pulse" class="widget-icon"></iconify-icon>
                            <h3>Recent Blood Requests</h3>
                        </div>
                        <div class="widget-request-list">
                            <?php if (!empty($recentRequests)): ?>
                                <?php foreach ($recentRequests as $rr): ?>
                                <div class="widget-request-item">
                                    <div class="request-left-info">
                                        <span class="badge bg-badge"><?php echo htmlspecialchars($rr['blood_group']); ?></span>
                                        <div>
                                            <span class="request-requester"><?php echo htmlspecialchars($rr['requester_name'] ?? 'Unknown'); ?></span>
                                            <span class="request-location"><?php echo htmlspecialchars($rr['location']); ?></span>
                                        </div>
                                    </div>
                                    <div class="request-right-info">
                                        <span class="badge urgency-<?php echo $rr['urgency']; ?>"><?php echo ucfirst($rr['urgency']); ?></span>
                                        <span class="badge status-<?php echo $rr['status']; ?>"><?php echo ucfirst($rr['status']); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="widget-no-data">No blood requests yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Admin Widgets -->
                <div class="profile-right-col">

                    <!-- Quick Overview -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:view-dashboard" class="widget-icon"></iconify-icon>
                            <h3>Quick Overview</h3>
                        </div>
                        <div class="widget-stats-grid">
                            <div class="widget-stat-item">
                                <span class="widget-stat-value"><?php echo $totalUsers; ?></span>
                                <span class="widget-stat-label">Users</span>
                            </div>
                            <div class="widget-stat-item">
                                <span class="widget-stat-value"><?php echo $totalDonors; ?></span>
                                <span class="widget-stat-label">Donors</span>
                            </div>
                            <div class="widget-stat-item">
                                <span class="widget-stat-value"><?php echo $totalCampaigns; ?></span>
                                <span class="widget-stat-label">Campaigns</span>
                            </div>
                            <div class="widget-stat-item highlight-stat">
                                <span class="widget-stat-value"><?php echo $pendingRequests; ?></span>
                                <span class="widget-stat-label">Pending</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account Security -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:shield-check" class="widget-icon"></iconify-icon>
                            <h3>Account Security</h3>
                        </div>
                        <div class="security-list">
                            <div class="security-item">
                                <div class="security-left">
                                    <iconify-icon icon="mdi:email-outline" style="color: #888;"></iconify-icon>
                                    <div>
                                        <span class="security-label">Email</span>
                                        <span class="security-value"><?php echo htmlspecialchars($admin['email']); ?></span>
                                    </div>
                                </div>
                                <span class="security-badge verified"><iconify-icon icon="mdi:check-circle"></iconify-icon> Verified</span>
                            </div>
                            <div class="security-item">
                                <div class="security-left">
                                    <iconify-icon icon="mdi:lock-outline" style="color: #888;"></iconify-icon>
                                    <div>
                                        <span class="security-label">Password</span>
                                        <span class="security-value">••••••••</span>
                                    </div>
                                </div>
                                <span class="security-badge secure"><iconify-icon icon="mdi:shield-check"></iconify-icon> Secure</span>
                            </div>
                            <div class="security-item">
                                <div class="security-left">
                                    <iconify-icon icon="mdi:badge-account" style="color: #888;"></iconify-icon>
                                    <div>
                                        <span class="security-label">Role</span>
                                        <span class="security-value"><?php echo ucfirst($admin['role']); ?></span>
                                    </div>
                                </div>
                                <span class="security-badge admin-badge"><iconify-icon icon="mdi:star"></iconify-icon> Admin</span>
                            </div>
                            <div class="security-item">
                                <div class="security-left">
                                    <iconify-icon icon="mdi:calendar-clock" style="color: #888;"></iconify-icon>
                                    <div>
                                        <span class="security-label">Account Age</span>
                                        <span class="security-value">
                                            <?php
                                            if ($accountAge->y > 0) echo $accountAge->y . ' year(s)';
                                            elseif ($accountAge->m > 0) echo $accountAge->m . ' month(s)';
                                            else echo $accountAge->d . ' day(s)';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request Fulfillment Rate -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:chart-arc" class="widget-icon"></iconify-icon>
                            <h3>Request Fulfillment</h3>
                        </div>
                        <div class="fulfillment-visual">
                            <?php
                            $fulfillRate = $totalRequests > 0 ? round(($fulfilledRequests / $totalRequests) * 100) : 0;
                            ?>
                            <div class="fulfillment-ring" style="--progress: <?php echo $fulfillRate; ?>;">
                                <span class="fulfillment-percent"><?php echo $fulfillRate; ?>%</span>
                            </div>
                            <div class="fulfillment-details">
                                <div class="fulfill-detail-row">
                                    <span class="fulfill-dot fulfilled"></span>
                                    <span>Fulfilled</span>
                                    <strong><?php echo $fulfilledRequests; ?></strong>
                                </div>
                                <div class="fulfill-detail-row">
                                    <span class="fulfill-dot pending"></span>
                                    <span>Pending</span>
                                    <strong><?php echo $pendingRequests; ?></strong>
                                </div>
                                <div class="fulfill-detail-row">
                                    <span class="fulfill-dot total"></span>
                                    <span>Total</span>
                                    <strong><?php echo $totalRequests; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:history" class="widget-icon"></iconify-icon>
                            <h3>Recent Registrations</h3>
                        </div>
                        <div class="activity-list">
                            <?php if (!empty($recentUsers)): ?>
                                <?php foreach ($recentUsers as $ru): ?>
                                <div class="activity-item">
                                    <div class="activity-avatar">
                                        <iconify-icon icon="mdi:account-circle" style="font-size: 28px; color: #555;"></iconify-icon>
                                    </div>
                                    <div class="activity-info">
                                        <span class="activity-name"><?php echo htmlspecialchars($ru['name']); ?></span>
                                        <span class="activity-detail"><?php echo htmlspecialchars($ru['email']); ?></span>
                                    </div>
                                    <span class="activity-time"><?php echo date('M d', strtotime($ru['created_at'])); ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="widget-no-data">No recent registrations</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="profile-widget">
                        <div class="widget-header">
                            <iconify-icon icon="mdi:lightning-bolt" class="widget-icon"></iconify-icon>
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="quick-actions-grid">
                            <a href="users.php" class="quick-action-btn">
                                <iconify-icon icon="mdi:account-group"></iconify-icon>
                                <span>Manage Users</span>
                            </a>
                            <a href="campaigns.php" class="quick-action-btn">
                                <iconify-icon icon="mdi:bullhorn"></iconify-icon>
                                <span>Campaigns</span>
                            </a>
                            <a href="requests.php" class="quick-action-btn">
                                <iconify-icon icon="mdi:clipboard-text"></iconify-icon>
                                <span>Requests</span>
                            </a>
                            <a href="index.php" class="quick-action-btn">
                                <iconify-icon icon="mdi:view-dashboard"></iconify-icon>
                                <span>Dashboard</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
