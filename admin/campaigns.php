<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$msgType = "";

// DELETE campaign
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = :id");
    $stmt->execute(['id' => intval($_GET['delete'])]);
    $message = "Campaign deleted successfully.";
    $msgType = "success";
}

// Search & filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterLocation = isset($_GET['location']) ? trim($_GET['location']) : '';

$sql = "SELECT * FROM campaigns WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND name LIKE :search";
    $params['search'] = "%$search%";
}
if ($filterLocation) {
    $sql .= " AND location LIKE :location";
    $params['location'] = "%$filterLocation%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique locations for filter
$locations = $pdo->query("SELECT DISTINCT location FROM campaigns ORDER BY location")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns — Vital Drop Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-top">
                <h1 class="page-title">Campaigns</h1>
                <a href="create_campaign.php" class="btn-primary">+ Create Campaign</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Search & Filter -->
            <form method="GET" class="filter-bar">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search for campaigns..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">🔍</button>
                </div>
                <div class="search-box">
                    <input type="text" name="location" placeholder="Filter by location..." value="<?php echo htmlspecialchars($filterLocation); ?>">
                    <button type="submit" class="search-btn">🔍</button>
                </div>
                <?php if ($search || $filterLocation): ?>
                    <a href="campaigns.php" class="clear-filters">✕ Clear</a>
                <?php endif; ?>
            </form>

            <!-- Campaign Cards -->
            <div class="campaign-grid">
                <?php foreach ($campaigns as $camp): ?>
                <div class="campaign-card">
                    <h3 class="campaign-name"><?php echo htmlspecialchars($camp['name']); ?></h3>
                    <div class="campaign-details">
                        <p><span class="detail-label">Location:</span> <?php echo htmlspecialchars($camp['location']); ?></p>
                        <p><span class="detail-label">Time:</span> <?php echo htmlspecialchars($camp['time_range']); ?></p>
                        <?php if ($camp['hospital_name']): ?>
                            <p><span class="detail-label">Hospital:</span> <?php echo htmlspecialchars($camp['hospital_name']); ?></p>
                        <?php endif; ?>
                        <p><span class="detail-label">Blood Groups:</span> <?php echo htmlspecialchars($camp['blood_groups']); ?></p>
                    </div>
                    <div class="campaign-actions">
                        <a href="edit_campaign.php?id=<?php echo $camp['id']; ?>" class="btn-edit-card">Edit</a>
                        <a href="?delete=<?php echo $camp['id']; ?>" class="btn-delete-card" onclick="return confirm('Delete this campaign?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($campaigns)): ?>
                <div class="no-data-card">
                    <p>No campaigns found. Create your first campaign!</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
