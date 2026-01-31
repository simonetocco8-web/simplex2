<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="m-0"><?= e($offer['protocol_full']) ?></h4>
    <div class="text-muted small"><?= e($offer['business_name']) ?> · <?= e($offer['service']) ?></div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="/offers">← Elenco</a>
    <a class="btn btn-outline-primary" href="/offers/new?id=<?= (int)$offer['id'] ?>">Modifica</a>
    <a class="btn btn-outline-dark" href="/offers/history?id=<?= (int)$offer['id'] ?>">Cronologia</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Dati offerta</h6>
        <div><strong>Servizio:</strong> <?= e($offer['service']) ?></div>
        <div><strong>Dettaglio:</strong> <?= e($offer['subservice'] ?: $offer['specific_service'] ?: '-') ?></div>
        <div><strong>Priorità:</strong> <?= e($offer['priority']) ?></div>
        <hr>
        <div><strong>Data offerta:</strong> <?= e($offer['offer_date']) ?></div>
        <div><strong>Validità (gg):</strong> <?= (int)$offer['validity_days'] ?></div>
        <div><strong>Scadenza:</strong> <?= e($offer['expiry_date']) ?></div>
        <hr>
        <div><strong>Sconto %:</strong> <?= e($offer['discount_pct'] ?? '-') ?></div>
        <div><strong>Pagamento:</strong> <?= e($offer['payment_terms'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Stato</h6>

        <div class="mb-2">
          <span class="badge text-bg-<?= $offer['status']==='aggiudicata'?'success':($offer['status']==='annullata'?'secondary':'primary') ?>">
            <?= e($offer['status']) ?>
          </span>
        </div>

        <form method="POST" action="/offers/status" class="row g-2 align-items-end">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$offer['id'] ?>">
          <div class="col-md-8">
            <label class="form-label">Cambia stato</label>
            <select class="form-select" name="status">
              <?php foreach (['bozza','inviata','aggiudicata','annullata'] as $s): ?>
                <option value="<?= e($s) ?>" <?= ($offer['status']===$s)?'selected':'' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Se imposti “aggiudicata” verrà creata automaticamente la commessa.</div>
          </div>
          <div class="col-md-4">
            <button class="btn btn-primary w-100">Aggiorna</button>
          </div>
        </form>

        <hr>

        <div><strong>Oggetto:</strong></div>
        <div class="border rounded p-2 bg-light" style="white-space:pre-wrap;"><?= e($offer['object_specs'] ?? '') ?></div>

        <div class="mt-3"><strong>Note:</strong></div>
        <div class="border rounded p-2 bg-light" style="white-space:pre-wrap;"><?= e($offer['notes'] ?? '') ?></div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Fasi di lavorazione</h6>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Tipologia</th><th>Importo €</th><th>Data prevista</th><th>Fatturazione</th><th>GG Uomo</th><th>Ore</th><th>Giorni</th>
              </tr>
            </thead>
            <tbody>
              <?php $tot = 0; foreach ($phases as $ph): $tot += (float)$ph['amount_eur']; ?>
                <tr>
                  <td><?= e($ph['phase_type']) ?></td>
                  <td><?= e(number_format((float)$ph['amount_eur'],2,',','.')) ?></td>
                  <td><?= e($ph['planned_date'] ?? '-') ?></td>
                  <td><?= ((int)$ph['is_billable']===1)?'Sì':'No' ?></td>
                  <td><?= e($ph['day_man_value_eur'] ?? '-') ?></td>
                  <td><?= e($ph['hours'] ?? '-') ?></td>
                  <td><?= e($ph['days'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$phases): ?>
                <tr><td colspan="7" class="text-muted">Nessuna fase</td></tr>
              <?php endif; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="1">Totale</th>
                <th><?= e(number_format($tot,2,',','.')) ?></th>
                <th colspan="5"></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
