<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="m-0">Cronologia Offerta</h4>
    <div class="text-muted small"><?= e($offer['protocol_full']) ?> · <?= e($offer['business_name']) ?></div>
  </div>
  <a class="btn btn-outline-secondary" href="/offers/view?id=<?= (int)$offer['id'] ?>">← Torna</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Data</th><th>Azione</th><th>Utente</th><th>Dettagli</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $l): ?>
          <tr>
            <td><?= e($l['changed_at']) ?></td>
            <td><?= e($l['action']) ?></td>
            <td><?= e($l['last_name'].' '.$l['first_name']) ?></td>
            <td class="text-muted small" style="white-space:pre-wrap;">
              <?= e($l['diff_json'] ?? '') ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">Nessuna modifica registrata</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
