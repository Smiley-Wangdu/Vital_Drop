<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$msgType = "";

/* ---------------- STATUS UPDATE (FIXED VALUES) ---------------- */
if (isset($_GET['status'], $_GET['id']) && is_numeric($_GET['id'])) {

    $newStatus = $_GET['status'];
    $reqId = intval($_GET['id']);

    $validStatuses = ['Active', 'Fulfilled', 'Cancelled', 'Expired'];

    if (in_array($newStatus, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE blood_requests SET status = :status WHERE id = :id");
        $stmt->execute([
            'status' => $newStatus,
            'id' => $reqId
        ]);

        $message = "Request status updated.";
        $msgType = "success";
    }
}

/* ---------------- DELETE REQUEST ---------------- */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM blood_requests WHERE id = :id");
    $stmt->execute(['id' => intval($_GET['delete'])]);

    $message = "Request deleted.";
    $msgType = "success";
}

/* ---------------- FILTER ---------------- */
$filterStatus = $_GET['filter_status'] ?? '';

$sql = "
SELECT 
    br.*,
    u.first_name,
    u.last_name,
    u.email
FROM blood_requests br
LEFT JOIN users u ON br.user_id = u.id
WHERE 1=1
";

$params = [];

if ($filterStatus) {
    $sql .= " AND br.status = :status";
    $params['status'] = $filterStatus;
}

$sql .= " ORDER BY br.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Requests</title>
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>

<body class="admin-body dark">

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">

<h1 class="page-title">Blood Requests</h1>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $msgType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- FILTER -->
<form method="GET" class="filter-bar">
    <select name="filter_status" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="Active" <?php echo $filterStatus === 'Active' ? 'selected' : ''; ?>>Active</option>
        <option value="Fulfilled" <?php echo $filterStatus === 'Fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
        <option value="Cancelled" <?php echo $filterStatus === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        <option value="Expired" <?php echo $filterStatus === 'Expired' ? 'selected' : ''; ?>>Expired</option>
    </select>

    <?php if ($filterStatus): ?>
        <a href="requests.php" class="clear-filters">Clear</a>
    <?php endif; ?>
</form>

<!-- TABLE -->
<div class="table-card">
<table class="admin-table">

<thead>
<tr>
    <th>ID</th>
    <th>Requester</th>
    <th>Blood Group</th>
    <th>Location</th>
    <th>Urgency</th>
    <th>Status</th>
    <th>Date</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($requests as $req): ?>
<tr>
    <td><?php echo $req['id']; ?></td>

    <td>
        <?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?>
    </td>

    <td>
        <span class="badge bg-badge">
            <?php echo htmlspecialchars($req['blood_group']); ?>
        </span>
    </td>

    <td><?php echo htmlspecialchars($req['location']); ?></td>

    <td>
        <span class="badge urgency-<?php echo $req['urgency']; ?>">
            <?php echo $req['urgency']; ?>
        </span>
    </td>

    <td>
        <span class="badge status-<?php echo $req['status']; ?>">
            <?php echo $req['status']; ?>
        </span>
    </td>

    <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>

    <td>
        <?php if ($req['status'] === 'Active'): ?>
            <a href="?id=<?php echo $req['id']; ?>&status=Fulfilled">✔</a>
            <a href="?id=<?php echo $req['id']; ?>&status=Cancelled">✖</a>
        <?php else: ?>
            <a href="?id=<?php echo $req['id']; ?>&status=Active">↻</a>
        <?php endif; ?>

        <a href="?delete=<?php echo $req['id']; ?>"
           onclick="return confirm('Delete request?')">🗑</a>
    </td>

</tr>
<?php endforeach; ?>

<?php if (empty($requests)): ?>
<tr>
    <td colspan="8">No requests found</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</main>

</body>
</html>