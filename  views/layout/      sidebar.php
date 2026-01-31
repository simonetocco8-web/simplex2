<?php $cfg = require __DIR__ . '/../../app/config/config.php'; ?>

<?php
require_once __DIR__ . '/../../app/repositories/NotificationRepository.php';
$u = current_user();
$unread = $u ? NotificationRepository::unreadCount((int)$u['id']) : 0;
?>

<div class="offcanvas-lg offcanvas-start bg-white border-end" tabindex="-1" id="sidebar">
  <div class="offcanvas-header d-lg-none">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="list-group list-group-flush">
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
   href="<?= e(($cfg['app']['base_url'] ?: '').'/') ?>">
  <span>Bacheca</span>
  <?php if ($unread > 0): ?>
    <span class="badge text-bg-primary rounded-pill"><?= (int)$unread ?></span>
  <?php endif; ?>
</a>
      <a class="list-group-item list-group-item-action" href="<?= e(($cfg['app']['base_url'] ?: '').'/companies') ?>">Aziende</a>
      <a class="list-group-item list-group-item-action" href="<?= e(($cfg['app']['base_url'] ?: '').'/offers') ?>">Offerte</a>
      <a class="list-group-item list-group-item-action" href="<?= e(($cfg['app']['base_url'] ?: '').'/jobs') ?>">Commesse</a>

      <div class="list-group-item fw-semibold text-uppercase small text-muted">Amministrazione</div>
      <a class="list-group-item list-group-item-action ps-4" href="<?= e(($cfg['app']['base_url'] ?: '').'/admin/production') ?>">Produzione</a>
      <a class="list-group-item list-group-item-action ps-4" href="<?= e(($cfg['app']['base_url'] ?: '').'/admin/invoices') ?>">Fatture</a>
      <a class="list-group-item list-group-item-action ps-4" href="<?= e(($cfg['app']['base_url'] ?: '').'/admin/payments') ?>">Pagamenti</a>

      <a class="list-group-item list-group-item-action" href="<?= e(($cfg['app']['base_url'] ?: '').'/users') ?>">Utenti</a>
      <a class="list-group-item list-group-item-action" href="<?= e(($cfg['app']['base_url'] ?: '').'/agenda') ?>">Impegni</a>
    </div>
  </div>
</div>

<main class="col-lg-10 ms-auto px-3 py-3">
  <?php if (!empty($flash)): ?>
    <?php foreach ($flash as $type => $msgs): ?>
      <?php foreach ($msgs as $m): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : e($type) ?>"><?= e($m) ?></div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>
