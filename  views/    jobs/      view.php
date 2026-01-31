<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="m-0"><?= e($job['protocol_full'] ?? ('Commessa #'.$job['id'])) ?></h4>
    <div class="text-muted small">
      Cliente: <?= e($job['business_name']) ?> · Offerta: <?= e($job['offer_protocol'] ?? '-') ?>
    </div>
  </div>
  <a class="btn btn-outline-secondary" href="/jobs">← Elenco</a>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Dati commessa</h6>
        <div><strong>Job Code:</strong> <?= e($job['job_code'] ?? '-') ?></div>
        <div><strong>Stato:</strong> <?= e($job['progress_status'] ?? '-') ?></div>
        <div><strong>Area:</strong> <?= e($job['area_code'] ?? '-') ?></div>
        <div><strong>Budget €:</strong> <?= e($job['budget_eur'] ?? '-') ?></div>
        <div class="text-muted small mt-2">Assegnata il: <?= e($job['assigned_at'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Assegnazione consulente</h6>

        <?php if (!$canAssign): ?>
          <div class="text-muted">Solo i responsabili di area possono assegnare la commessa.</div>
        <?php else: ?>
          <form method="POST" action="/jobs/assign" class="row g-2">
            <?= csrf_field() ?>
            <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>">

            <div class="col-4">
              <label class="form-label">Area</label>
              <select class="form-select" name="area_code">
                <option value="">--</option>
                <?php foreach (['GD','FI','PC'] as $a): ?>
                  <option value="<?= e($a) ?>" <?= (($job['area_code'] ?? '')===$a)?'selected':'' ?>><?= e($a) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-8">
              <label class="form-label">Consulente incaricato</label>
              <select class="form-select" name="assigned_user_id" required>
                <option value="">-- seleziona --</option>
                <?php foreach ($consultants as $c): ?>
                  <option value="<?= (int)$c['id'] ?>" <?= ((int)($job['assigned_user_id'] ?? 0)===(int)$c['id'])?'selected':'' ?>>
                    <?= e($c['last_name'].' '.$c['first_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12">
              <button class="btn btn-primary w-100">Assegna & Notifica</button>
            </div>

            <div class="form-text">
              Verrà inviata una notifica in bacheca al consulente selezionato.
            </div>
          </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
