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

<div class="tabs-content-wrapper">
    <div class="mr-header">
        <div class="mr-icon">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <h2>Attended Campaigns</h2>
            <p>Your participation in life-saving events</p>
        </div>
    </div>

    <?php if ($campaigns): ?>
        <?php foreach ($campaigns as $c): ?>
            <div class="mr-card">
                <div class="mr-card-top">
                    <div class="mr-badges">
                        <span class="mr-badge badge-active">Joined</span>
                    </div>
                </div>
                <div class="mr-info">
                    <div class="mr-ef-full">Campaign<span><?= htmlspecialchars($c['name']) ?></span></div>
                    <div>Location<span><?= htmlspecialchars($c['location']) ?></span></div>
                    <div>Time<span><?= htmlspecialchars($c['time_range']) ?></span></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="mr-empty">
            <p>No campaigns attended yet.</p>
        </div>
    <?php endif; ?>
</div>