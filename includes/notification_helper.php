<?php
// includes/notification_helper.php

/**
 * Creates a new notification for a specific user.
 *
 * @param PDO $pdo The database connection object.
 * @param int $user_id The ID of the user receiving the notification.
 * @param string $message The notification message.
 * @param string $type The type of notification (e.g., 'request_match', 'donor_response').
 * @param int|null $related_id An optional ID of the related entity (e.g., blood request ID).
 * @return bool True if successful, false otherwise.
 */
function create_notification($pdo, $user_id, $message, $type = null, $related_id = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, type, related_id)
            VALUES (:user_id, :message, :type, :related_id)
        ");
        return $stmt->execute([
            'user_id' => $user_id,
            'message' => $message,
            'type' => $type,
            'related_id' => $related_id
        ]);
    } catch (PDOException $e) {
        // Log error or handle gracefully
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}
?>
