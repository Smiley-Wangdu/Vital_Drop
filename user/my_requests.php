<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    } else {
        header("Location: ../auth/login.php");
    }
    exit;
}

$user_id = $_SESSION['user_id'];

// Auto-expire stale active requests 
$pdo->exec("UPDATE blood_requests SET status = 'Expired' WHERE expires_at < NOW() AND status = 'Active'");

// AJAX POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit;
    }

    if ($action === 'cancel' || $action === 'fulfill') {
        $status = $action === 'cancel' ? 'Cancelled' : 'Fulfilled';

        $stmt = $pdo->prepare("
            UPDATE blood_requests 
            SET status=:s 
            WHERE id=:id AND user_id=:uid AND status='Active'
        ");

        $stmt->execute([
            's' => $status,
            'id' => $id,
            'uid' => $user_id
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'edit') {
        $bg = trim($_POST['blood_group'] ?? '');
        $hn = trim($_POST['hospital_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $cn = trim($_POST['contact_number'] ?? '');
        $ur = intval($_POST['units_required'] ?? 1);
        $ug = ($_POST['urgency'] ?? '') === 'Emergency' ? 'Urgent' : 'Normal';

        if (!$bg || !$hn || !$loc || !$cn) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE blood_requests
            SET blood_group=:bg,
                hospital_name=:hn,
                location=:loc,
                contact_number=:cn,
                units_required=:ur,
                urgency=:ug
            WHERE id=:id AND user_id=:uid AND status='Active'
        ");

        $stmt->execute([
            'bg' => $bg,
            'hn' => $hn,
            'loc' => $loc,
            'cn' => $cn,
            'ur' => $ur,
            'ug' => $ug,
            'id' => $id,
            'uid' => $user_id
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

// GET requests
$stmt = $pdo->prepare("
    SELECT * FROM blood_requests
    WHERE user_id = :uid
    ORDER BY
        CASE WHEN status  = 'Active' THEN 0 ELSE 1 END,
        CASE WHEN urgency = 'Urgent' THEN 0 ELSE 1 END,
        created_at DESC
");
$stmt->execute(['uid' => $user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$active = array_filter($requests, fn($r) => $r['status'] === 'Active');
$inactive = array_filter($requests, fn($r) => $r['status'] !== 'Active');
?>

<div class="tabs-content-wrapper" id="my-requests-wrap">

    <div class="mr-header">
        <div class="mr-icon">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <div>
            <h2>My Blood Requests</h2>
            <p>View, edit, or manage your active and past requests</p>
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div class="mr-empty">
            <p>You have no blood requests yet.</p>
        </div>
    <?php else: ?>

        <!-- ACTIVE -->
        <?php if ($active): ?>
            <div class="mr-section-head">
                <span class="mr-section-title">
                    Active Requests <span class="mr-count">(<?= count($active) ?>)</span>
                </span>
            </div>

            <?php foreach ($active as $r):
                $isUrgent = $r['urgency'] === 'Urgent';
                $expires = strtotime($r['expires_at']);
                $daysLeft = max(0, ceil(($expires - time()) / 86400));
                ?>

                <div class="mr-card <?= $isUrgent ? 'urgent' : '' ?>" id="mr-card-<?= $r['id'] ?>">

                    <div class="mr-card-top">

                        <div class="mr-badges">
                            <span class="mr-badge badge-blood">
                                <?= htmlspecialchars($r['blood_group']) ?>
                            </span>
                            <span class="mr-badge <?= $isUrgent ? 'badge-urgent' : 'badge-normal' ?>">
                                <?= $isUrgent ? 'EMERGENCY' : 'Normal' ?>
                            </span>
                            <span class="mr-badge badge-active">Active</span>
                        </div>

                        <!-- 3 DOT MENU -->
                        <div class="mr-menu-wrapper">
                            <button class="mr-menu-btn" onclick="toggleMrMenu(<?= $r['id'] ?>)">⋮</button>

                            <div class="mr-menu" id="mr-menu-<?= $r['id'] ?>">
                                <button onclick="mrToggleEdit(<?= $r['id'] ?>); toggleMrMenu(<?= $r['id'] ?>)">Edit</button>
                                <button onclick="mrUpdate(<?= $r['id'] ?>,'fulfill')">Mark Fulfilled</button>
                                <button onclick="mrUpdate(<?= $r['id'] ?>,'cancel')">Cancel Request</button>
                            </div>
                        </div>

                    </div>

                    <div class="mr-info">
                        <div>Hospital<span>
                                <?= htmlspecialchars($r['hospital_name']) ?>
                            </span></div>
                        <div>Location<span>
                                <?= htmlspecialchars($r['location']) ?>
                            </span></div>
                        <div>Contact<span>
                                <?= htmlspecialchars($r['contact_number']) ?>
                            </span></div>
                        <div>Units Needed<span>
                                <?= (int) $r['units_required'] ?>
                            </span></div>
                    </div>

                    <p class="mr-expires">
                        Expires in
                        <?= $daysLeft ?> days —
                        <?= date('M d, Y', $expires) ?>
                    </p>

                    <div class="mr-edit-form" id="mr-edit-<?= $r['id'] ?>">

                        <div class="mr-ef-grid">

                            <div>
                                <label class="mr-ef-label">Blood Group</label>
                                <input class="mr-ef-input" id="ef-bg-<?= $r['id'] ?>" value="<?= $r['blood_group'] ?>">
                            </div>

                            <div>
                                <label class="mr-ef-label">Units</label>
                                <input class="mr-ef-input" id="ef-ur-<?= $r['id'] ?>" value="<?= $r['units_required'] ?>">
                            </div>

                            <div class="mr-ef-full">
                                <label class="mr-ef-label">Hospital</label>
                                <input class="mr-ef-input" id="ef-hn-<?= $r['id'] ?>" value="<?= $r['hospital_name'] ?>">
                            </div>

                            <div>
                                <label class="mr-ef-label">Location</label>
                                <input class="mr-ef-input" id="ef-loc-<?= $r['id'] ?>" value="<?= $r['location'] ?>">
                            </div>

                            <div>
                                <label class="mr-ef-label">Contact</label>
                                <input class="mr-ef-input" id="ef-cn-<?= $r['id'] ?>" value="<?= $r['contact_number'] ?>">
                            </div>

                            <!-- Urgency field -->
                            <div>
                                <label class="mr-ef-label">Urgency</label>
                                <select class="mr-ef-input" id="ef-ug-<?= $r['id'] ?>">
                                    <option value="Normal" <?= $r['urgency'] == 'Normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="Emergency" <?= $r['urgency'] == 'Urgent' ? 'selected' : '' ?>>Emergency</option>
                                </select>
                            </div>

                        </div>

                        <div class="mr-ef-row">
                            <button class="mr-ef-save" onclick="mrSaveEdit(<?= $r['id'] ?>)">Save Changes</button>
                            <button class="mr-ef-discard" onclick="mrToggleEdit(<?= $r['id'] ?>)">Cancel</button>
                        </div>

                    </div>

                </div>

            <?php endforeach; ?>
        <?php endif; ?>

        <!-- PAST -->
        <?php if ($inactive): ?>
            <div class="mr-section-head">
                <span class="mr-section-title">
                    Past Requests <span class="mr-count-past">(<?= count($inactive) ?>)</span>
                </span>
            </div>

            <?php foreach ($inactive as $r):
                $sc = strtolower($r['status']);
                ?>

                <div class="mr-card">

                    <div class="mr-card-top">

                        <div class="mr-badges">
                            <span class="mr-badge badge-blood">
                                <?= htmlspecialchars($r['blood_group']) ?>
                            </span>
                            <span class="mr-badge badge-<?= $sc ?>">
                                <?= htmlspecialchars($r['status']) ?>
                            </span>
                        </div>

                    </div>

                    <div class="mr-info">
                        <div>Hospital<span>
                                <?= htmlspecialchars($r['hospital_name']) ?>
                            </span></div>
                        <div>Location<span>
                                <?= htmlspecialchars($r['location']) ?>
                            </span></div>
                        <div>Contact<span>
                                <?= htmlspecialchars($r['contact_number']) ?>
                            </span></div>
                        <div>Units<span>
                                <?= (int) $r['units_required'] ?>
                            </span></div>
                    </div>

                    <p class="mr-footer-date">
                        Created
                        <?= date('M d, Y', strtotime($r['created_at'])) ?>
                    </p>

                </div>

            <?php endforeach; ?>
        <?php endif; ?>

    <?php endif; ?>

</div>