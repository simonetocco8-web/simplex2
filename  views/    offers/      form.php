<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<?php
$isEdit = !empty($offer);
$val = function($k, $default='') use ($offer) { return $offer[$k] ?? $default; };
$selectedService = $val('service','');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0"><?= $isEdit ? 'Modifica Offerta' : 'Nuova Offerta' ?></h4>
  <a class="btn btn-outline-secondary" href="/offers">← Torna all’elenco</a>
</div>

<form method="POST" action="/offers/save" class="card" id="offerForm">
  <div class="card-body">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$offer['id'] ?>"><?php endif; ?>

    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <label class="form-label">Cliente (Azienda) *</label>
        <select class="form-select" name="company_id" required>
          <option value="">-- seleziona --</option>
          <?php foreach ($companies as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ((int)$val('company_id',0)===(int)$c['id'])?'selected':'' ?>>
              <?= e($c['business_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Servizio *</label>
        <select class="form-select" name="service" id="serviceSelect" required>
          <option value="">-- seleziona --</option>
          <?php foreach ($services as $s): ?>
            <option value="<?= e($s) ?>" <?= ($selectedService===$s)?'selected':'' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4" id="subserviceWrap">
        <label class="form-label">SottoServizio (SGA) *</label>
        <select class="form-select" name="subservice" id="subserviceSelect">
          <option value="">-- seleziona --</option>
          <?php foreach ($subservicesSga as $ss): ?>
            <option value="<?= e($ss) ?>" <?= ($val('subservice','')===$ss)?'selected':'' ?>><?= e($ss) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4 d-none" id="specificWrap">
        <label class="form-label">Servizio Specifico *</label>
        <select class="form-select" name="specific_service" id="specificSelect">
          <option value="">-- seleziona --</option>
        </select>
      </div>
    </div>

    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <label class="form-label">Specifiche Oggetto</label>
        <textarea class="form-control" rows="2" name="object_specs"><?= e($val('object_specs','')) ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Sede di erogazione del servizio</label>
        <input class="form-control" name="service_location" value="<?= e($val('service_location','')) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Priorità *</label>
        <select class="form-select" name="priority" required>
          <?php foreach (['alto','medio','basso'] as $p): ?>
            <option value="<?= e($p) ?>" <?= ($val('priority','')===$p)?'selected':'' ?>><?= e($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <label class="form-label">RCO *</label>
        <select class="form-select" name="rco_user_id" required>
          <option value="">-- seleziona --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= ((int)$val('rco_user_id',0)===(int)$u['id'])?'selected':'' ?>>
              <?= e($u['last_name'].' '.$u['first_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Segnalato da</label>
        <select class="form-select" name="referred_by_user_id">
          <option value="">--</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= ((int)$val('referred_by_user_id',0)===(int)$u['id'])?'selected':'' ?>>
              <?= e($u['last_name'].' '.$u['first_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Modalità di Pagamento</label>
        <select class="form-select" name="payment_terms">
          <option value="">--</option>
          <?php foreach ($paymentTerms as $pt): ?>
            <option value="<?= e($pt) ?>" <?= ($val('payment_terms','')===$pt)?'selected':'' ?>><?= e($pt) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row g-2 mb-3">
      <div class="col-md-3">
        <label class="form-label">Data Offerta *</label>
        <input type="date" class="form-control" name="offer_date" id="offerDate" required
               value="<?= e($val('offer_date','')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Validità (giorni) *</label>
        <input type="number" class="form-control" name="validity_days" id="validityDays" required min="1"
               value="<?= e($val('validity_days','')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Data di Scadenza *</label>
        <input type="date" class="form-control" name="expiry_date" id="expiryDate" required
               value="<?= e($val('expiry_date','')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">% Sconto (0..100)</label>
        <input type="number" class="form-control" name="discount_pct" min="0" max="100"
               value="<?= e($val('discount_pct','')) ?>">
      </div>
    </div>

    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <label class="form-label">Promotore</label>
        <select class="form-select" name="promoter_company_id" id="promoterCompany">
          <option value="">--</option>
          <?php foreach ($promoters as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= ((int)$val('promoter_company_id',0)===(int)$p['id'])?'selected':'' ?>>
              <?= e($p['business_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Se selezioni promotore, indica anche commissione.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Commissione promotore (tipo)</label>
        <select class="form-select" name="promoter_commission_type" id="promoterCommType">
          <option value="">--</option>
          <option value="percent" <?= ($val('promoter_commission_type','')==='percent')?'selected':'' ?>>Percentuale (%)</option>
          <option value="fixed" <?= ($val('promoter_commission_type','')==='fixed')?'selected':'' ?>>Valore fisso (€)</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Commissione promotore (valore)</label>
        <input class="form-control" name="promoter_commission_value" id="promoterCommValue"
               value="<?= e($val('promoter_commission_value','')) ?>" placeholder="es. 10 oppure 250,00">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Note</label>
      <textarea class="form-control" rows="2" name="notes"><?= e($val('notes','')) ?></textarea>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="text-uppercase text-muted m-0">Fasi di lavorazioni *</h6>
      <button type="button" class="btn btn-sm btn-outline-primary" id="addPhaseBtn">+ Aggiungi fase</button>
    </div>

    <div class="table-responsive">
      <table class="table table-sm align-middle" id="phasesTable">
        <thead>
          <tr>
            <th>Tipologia</th>
            <th>Importo €</th>
            <th>Data prevista</th>
            <th>Fatturazione</th>
            <th>Valore Giorno Uomo €</th>
            <th>Ore</th>
            <th>Giorni</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if ($phases): ?>
            <?php foreach ($phases as $i => $ph): ?>
              <tr>
                <td>
                  <select class="form-select form-select-sm" name="phase[<?= $i ?>][phase_type]">
                    <option value="apertura" <?= ($ph['phase_type']==='apertura')?'selected':'' ?>>apertura</option>
                    <option value="chiusura" <?= ($ph['phase_type']==='chiusura')?'selected':'' ?>>chiusura</option>
                  </select>
                </td>
                <td><input class="form-control form-control-sm" name="phase[<?= $i ?>][amount_eur]" value="<?= e($ph['amount_eur']) ?>"></td>
                <td><input type="date" class="form-control form-control-sm" name="phase[<?= $i ?>][planned_date]" value="<?= e($ph['planned_date'] ?? '') ?>"></td>
                <td class="text-center">
                  <input type="checkbox" class="form-check-input" name="phase[<?= $i ?>][is_billable]" value="1" <?= ((int)$ph['is_billable']===1)?'checked':'' ?>>
                </td>
                <td><input class="form-control form-control-sm" name="phase[<?= $i ?>][day_man_value_eur]" value="<?= e($ph['day_man_value_eur'] ?? '') ?>"></td>
                <td><input class="form-control form-control-sm" name="phase[<?= $i ?>][hours]" value="<?= e($ph['hours'] ?? '') ?>"></td>
                <td><input class="form-control form-control-sm" name="phase[<?= $i ?>][days]" value="<?= e($ph['days'] ?? '') ?>"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-phase">×</button></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr class="text-muted">
              <td colspan="8">Nessuna fase inserita. Clicca “Aggiungi fase”.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <div class="card-footer d-flex justify-content-between align-items-center">
    <div class="small text-muted">
      <?php if ($isEdit): ?>Protocollo: <strong><?= e($val('protocol_full','')) ?></strong><?php endif; ?>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/offers">Annulla</a>
      <button class="btn btn-primary"><?= $isEdit ? 'Salva modifiche' : 'Crea Offerta' ?></button>
    </div>
  </div>
</form>

<script>
window.IBIS_OFFER_LOOKUPS = {
  service: "<?= e($selectedService) ?>",
  specificSelected: "<?= e($val('specific_service','')) ?>",
  lists: {
    "SICUREZZA": <?= json_encode($specificSicurezza, JSON_UNESCAPED_UNICODE) ?>,
    "FORMAZIONE": <?= json_encode($specificFormazione, JSON_UNESCAPED_UNICODE) ?>,
    "FINANZA AGEVOLATA": <?= json_encode($specificFinanza, JSON_UNESCAPED_UNICODE) ?>,
    "CONSULENZA SOA": <?= json_encode($specificSOA, JSON_UNESCAPED_UNICODE) ?>,
    "ALTRE CONSULENZE": <?= json_encode($specificAltre, JSON_UNESCAPED_UNICODE) ?>
  }
};
</script>
<script src="/assets/js/offers.js"></script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
