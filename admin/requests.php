<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$msgType = "";

// Update status
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $newStatus = $_GET['status'];
    $reqId = intval($_GET['id']);
    if (in_array($newStatus, ['pending', 'fulfilled', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE blood_requests SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $reqId]);
        $message = "Request status updated.";
        $msgType = "success";
    }
}

// Delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM blood_requests WHERE id = :id");
    $stmt->execute(['id' => intval($_GET['delete'])]);
    $message = "Request deleted.";
    $msgType = "success";
}

// Fetch requests
$filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$sql = "SELECT r.*, u.name as requester_name, u.email as requester_email FROM blood_requests r LEFT JOIN users u ON r.requester_id = u.id WHERE 1=1";
$params = [];

if ($filterStatus) {
    $sql .= " AND r.status = :status";
    $params['status'] = $filterStatus;
}

$sql .= " ORDER BY CASE WHEN r.urgency = 'Emergency' THEN 1 ELSE 2 END, r.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Requests — Vital Drop Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="admin-body dark">

    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-top">
                <h1 class="page-title">Blood Requests</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="GET" class="filter-bar">
                <select name="filter_status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="fulfilled" <?php echo $filterStatus === 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                    <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <?php if ($filterStatus): ?>
                    <a href="requests.php" class="clear-filters">✕ Clear</a>
                <?php endif; ?>
            </form>

            <div class="table-card">
                <div class="table-header">
                    <h3>All Requests (<?php echo count($requests); ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester</th>
                                <th>Blood Group</th>
                                <th>Location</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?php echo $req['id']; ?></td>
                                <td><?php echo htmlspecialchars($req['requester_name'] ?? 'Unknown'); ?></td>
                                <td><span class="badge bg-badge"><?php echo htmlspecialchars($req['blood_group']); ?></span></td>
                                <td><?php echo htmlspecialchars($req['location']); ?></td>
                                <td><span class="badge urgency-<?php echo $req['urgency']; ?>"><?php echo ucfirst($req['urgency']); ?></span></td>
                                <td><span class="badge status-<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($req['notes'] ?? '-'); ?></td>
                                <td><?php echo date('M d', strtotime($req['created_at'])); ?></td>
                                <td class="actions-cell">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a href="?id=<?php echo $req['id']; ?>&status=fulfilled" class="btn-fulfill" title="Mark Fulfilled">Fulfill</a>
                                        <a href="?id=<?php echo $req['id']; ?>&status=cancelled" class="btn-cancel-req" title="Cancel">Cancel</a>
                                    <?php elseif ($req['status'] === 'cancelled' || $req['status'] === 'fulfilled'): ?>
                                        <a href="?id=<?php echo $req['id']; ?>&status=pending" class="btn-edit" title="Reopen">Reopen</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $req['id']; ?>" class="btn-delete" title="Delete" onclick="return confirm('Delete this request?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($requests)): ?>
                            <tr><td colspan="9" class="no-data">No blood requests yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
