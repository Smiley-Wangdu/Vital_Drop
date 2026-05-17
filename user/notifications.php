<?php
session_start();
require '../config/db.php';

// SECURITY
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];

// FETCH NOTIFICATIONS
$stmt = $pdo->prepare("
    SELECT id, message, is_read, created_at, type, related_id 
    FROM notifications 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
");
$stmt->execute(['user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="vd-dashboard-wrapper">
    <!-- HEADER -->
    <header class="vd-top-header">
        <div class="vd-page-title-wrap">
            <div class="vd-page-icon">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="vd-page-title">Notifications</div>
        </div>
        <button onclick="window.markAllAsRead()" class="notification-btn notification-btn-secondary" style="margin-left: auto;">Mark all as read</button>
    </header>

    <div class="notification-list">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card <?php echo $notification['is_read'] ? '' : 'unread'; ?>" id="notif-<?php echo $notification['id']; ?>" onclick="window.handleNotificationClick(event, <?php echo $notification['id']; ?>, '<?php echo htmlspecialchars($notification['type'] ?? ''); ?>', <?php echo $notification['related_id'] ? $notification['related_id'] : 'null'; ?>)">
                    <div class="notification-content">
                        <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                        <div class="notification-time">
                            <i class="fa-regular fa-clock"></i> 
                            <?php echo date('M j, Y, g:i a', strtotime($notification['created_at'])); ?>
                        </div>
                    </div>
                    <?php if (!$notification['is_read']): ?>
                        <div class="notification-actions">
                            <button class="notification-btn" onclick="event.stopPropagation(); window.markAsRead(<?php echo $notification['id']; ?>)">Mark as read</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="vd-badges-placeholder" style="margin-top: 50px;">
                <p>You have no notifications yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

