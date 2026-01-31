<?php
class NotificationService {

  public static function notify(PDO $pdo, int $userId, string $title, string $message, ?string $link=null): void {
    $st = $pdo->prepare("INSERT INTO notifications(user_id,title,message,link_url) VALUES(?,?,?,?)");
    $st->execute([$userId, $title, $message, $link]);
  }

  public static function notifyMany(PDO $pdo, array $userIds, string $title, string $message, ?string $link=null): void {
    $st = $pdo->prepare("INSERT INTO notifications(user_id,title,message,link_url) VALUES(?,?,?,?)");
    foreach ($userIds as $uid) {
      $st->execute([(int)$uid, $title, $message, $link]);
    }
  }
}
