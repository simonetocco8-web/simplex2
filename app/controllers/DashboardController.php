<?php
require __DIR__ . '/../repositories/NotificationRepository.php';

class DashboardController {
  public static function index() {
    $u = current_user();
    $notifications = NotificationRepository::listForUser((int)$u['id'], 80);
    $unread = NotificationRepository::unreadCount((int)$u['id']);
    require __DIR__ . '/../../views/dashboard/index.php';
  }

  public static function markRead() {
    csrf_verify();
    $u = current_user();
    $id = (int)($_POST['id'] ?? 0);
    NotificationRepository::markRead((int)$u['id'], $id);
    redirect('/');
  }

  public static function markAllRead() {
    csrf_verify();
    $u = current_user();
    NotificationRepository::markAllRead((int)$u['id']);
    redirect('/');
  }
}
