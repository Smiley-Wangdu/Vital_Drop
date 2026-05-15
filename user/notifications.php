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
    SELECT id, message, is_read, created_at 
    FROM notifications 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
");
$stmt->execute(['user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="padding-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #fff; font-size: 28px;">Notifications</h2>
        <button onclick="window.markAllAsRead()" class="notification-btn" style="background: #333; color: #fff; border-color: #555;">Mark all as read</button>
    </div>

    <div class="notification-list">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card <?php echo $notification['is_read'] ? '' : 'unread'; ?>" id="notif-<?php echo $notification['id']; ?>" onclick="window.markAsRead(<?php echo $notification['id']; ?>)">
                    <div class="notification-content">
                        <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                        <div class="notification-time"><?php echo date('M j, Y, g:i a', strtotime($notification['created_at'])); ?></div>
                    </div>
                    <?php if (!$notification['is_read']): ?>
                        <div class="notification-actions">
                            <button class="notification-btn" onclick="event.stopPropagation(); window.markAsRead(<?php echo $notification['id']; ?>)">Mark as read</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #aaa; text-align: center; margin-top: 50px;">You have no notifications.</p>
        <?php endif; ?>
    </div>
</div>
