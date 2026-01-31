<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="m-0">Bacheca</h4>
    <div class="text-muted small">Notifiche: <?= (int)$unread ?> non lette</div>
  </div>
  <form method="POST" action="/dashboard/read-all" class="m-0">
    <?= csrf_field() ?>
    <button class="btn btn-outline-primary" <?= $unread===0 ? 'disabled':'' ?>>Segna tutto come letto</button>
  </form>
</div>

<div class="card">
  <div class="list-group list-group-flush">
    <?php foreach ($notifications as $n): ?>
      <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
        <div class="flex-grow-1">
          <div class="d-flex align-items-center gap-2">
            <?php if ((int)$n['is_read'] === 0): ?>
              <span class="badge text-bg-primary">Nuovo</span>
            <?php else: ?>
              <span class="badge text-bg-secondary">Letto</span>
            <?php endif; ?>
            <div class="fw-semibold"><?= e($n['title']) ?></div>
          </div>

          <div class="text-muted small mt-1"><?= e($n['created_at']) ?></div>

          <div class="mt-2" style="white-space:pre-wrap;"><?= e($n['message']) ?></div>

          <?php if (!empty($n['link_url'])): ?>
            <div class="mt-2">
              <a href="<?= e($n['link_url']) ?>" class="btn btn-sm btn-outline-dark">Apri</a>
            </div>
          <?php endif; ?>
        </div>

        <div class="text-end">
          <?php if ((int)$n['is_read'] === 0): ?>
            <form method="POST" action="/dashboard/read" class="m-0">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
              <button class="btn btn-sm btn-outline-secondary">Segna letto</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (!$notifications): ?>
      <div class="list-group-item text-muted text-center py-4">Nessuna notifica</div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
