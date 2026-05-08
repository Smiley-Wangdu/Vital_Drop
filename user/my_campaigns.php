<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Not logged in";
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT c.name, c.location, c.time_range
    FROM campaign_participants cp
    JOIN campaigns c ON cp.campaign_id = c.id
    WHERE cp.user_id = ?
");
$stmt->execute([$user_id]);
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3 style="color:white; margin-bottom: 20px;">Attended Campaigns</h3>

<?php if ($campaigns): ?>
    <?php foreach ($campaigns as $c): ?>
        <div class="vd-request-card">
            <p><?= htmlspecialchars($c['name']) ?></p>
            <p><?= htmlspecialchars($c['location']) ?></p>
            <p><?= htmlspecialchars($c['time_range']) ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="color:#aaa;">No campaigns attended</p>
<?php endif; ?>