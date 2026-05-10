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
    SELECT * FROM donations 
    WHERE donor_id = ? 
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="tabs-content-wrapper">
    <div class="mr-header">
        <div class="mr-icon">
            <i class="fa-solid fa-hand-holding-heart"></i>
        </div>
        <div>
            <h2>My Donation History</h2>
            <p>Track your contributions and impact</p>
        </div>
    </div>

    <?php if ($donations): ?>
        <?php foreach ($donations as $d): ?>
            <div class="mr-card">
                <div class="mr-card-top">
                    <div class="mr-badges">
                        <span class="mr-badge badge-blood"><?= htmlspecialchars($d['blood_group'] ?? 'N/A') ?></span>
                        <span class="mr-badge badge-active">Donated</span>
                    </div>
                </div>
                <div class="mr-info">
                    <div>Hospital<span><?= htmlspecialchars($d['hospital_name'] ?? 'N/A') ?></span></div>
                    <div>Date<span><?= date('M d, Y', strtotime($d['created_at'])) ?></span></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="mr-empty">
            <p>No donations yet.</p>
        </div>
    <?php endif; ?>
</div>