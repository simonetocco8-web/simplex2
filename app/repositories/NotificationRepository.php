<?php
class NotificationRepository {

  public static function unreadCount(int $userId): int {
    $st = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $st->execute([$userId]);
    return (int)$st->fetchColumn();
  }

  public static function listForUser(int $userId, int $limit=50): array {
    $st = db()->prepare("SELECT * FROM notifications
                         WHERE user_id=?
                         ORDER BY is_read ASC, created_at DESC
                         LIMIT $limit");
    $st->execute([$userId]);
    return $st->fetchAll();
  }

  public static function markRead(int $userId, int $notifId): void {
    $st = db()->prepare("UPDATE notifications
                         SET is_read=1, read_at=NOW()
                         WHERE id=? AND user_id=?");
    $st->execute([$notifId, $userId]);
  }

  public static function markAllRead(int $userId): void {
    $st = db()->prepare("UPDATE notifications
                         SET is_read=1, read_at=NOW()
                         WHERE user_id=? AND is_read=0");
    $st->execute([$userId]);
  }
}
