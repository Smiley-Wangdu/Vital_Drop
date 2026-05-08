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

<h3 style="color:white; margin-bottom: 20px;">My Donation History</h3>

<?php if ($donations): ?>
    <?php foreach ($donations as $d): ?>
        <div class="vd-request-card">
            <p>Blood Group: <?= htmlspecialchars($d['blood_group'] ?? 'N/A') ?></p>
            <p>Hospital: <?= htmlspecialchars($d['hospital_name'] ?? 'N/A') ?></p>
            <p>Date: <?= htmlspecialchars($d['created_at'] ?? 'N/A') ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="color:#aaa;">No donations yet</p>
<?php endif; ?>