<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<?php
$isEdit = !empty($company);
$val = function($k, $default='') use ($company) {
  if (!$company) return $default;
  return $company[$k] ?? $default;
};
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0"><?= $isEdit ? 'Modifica Azienda' : 'Nuova Azienda' ?></h4>
  <a class="btn btn-outline-secondary" href="/companies">← Torna all’elenco</a>
</div>

<form method="POST" action="/companies/save" class="card">
  <div class="card-body">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= (int)$company['id'] ?>">
    <?php endif; ?>

    <!-- SEZIONE: ANAGRAFICA -->
    <h6 class="text-uppercase text-muted mb-2">Anagrafica</h6>
    <div class="row g-2 mb-3">
      <div class="col-md-3">
        <label class="form-label">Partita IVA *</label>
        <input class="form-control" name="vat_number" maxlength="11" required
               value="<?= e($val('vat_number')) ?>" placeholder="11 numeri">
      </div>

      <div class="col-md-3">
        <label class="form-label">Codice Fiscale</label>
        <input class="form-control" name="tax_code" maxlength="16"
               value="<?= e($val('tax_code','')) ?>" placeholder="11 cifre o 16 alfanumerici">
      </div>

      <div class="col-md-6">
        <label class="form-label">Ragione Sociale *</label>
        <input class="form-control" name="business_name" maxlength="30" required
               value="<?= e($val('business_name')) ?>" placeholder="Max 30 caratteri">
      </div>

      <div class="col-md-4">
        <label class="form-label">IBAN</label>
        <input class="form-control" name="iban" maxlength="34"
               value="<?= e($val('iban','')) ?>" placeholder="IT..">
      </div>

      <div class="col-md-4">
        <label class="form-label">Codice fatturazione</label>
        <input class="form-control" name="invoicing_code"
               value="<?= e($val('invoicing_code','')) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Tipologia di Azienda *</label>
        <div class="border rounded p-2 bg-light">
          <?php foreach ($types as $t): ?>
            <?php $checked = in_array((int)$t['id'], $selectedTypeIds, true); ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox"
                     name="type_ids[]" value="<?= (int)$t['id'] ?>" <?= $checked ? 'checked' : '' ?>>
              <label class="form-check-label"><?= e($t['name']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="form-text">Puoi selezionare più valori.</div>
      </div>
    </div>

    <!-- SEZIONE: CONTATTI -->
    <h6 class="text-uppercase text-muted mb-2">Contatti</h6>
    <div class="row g-2 mb-3">
      <div class="col-md-3">
        <label class="form-label">Telefono</label>
        <input class="form-control" name="phone" value="<?= e($val('phone','')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">PEC</label>
        <input class="form-control" name="pec_email" value="<?= e($val('pec_email','')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" value="<?= e($val('email','')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Sito Web</label>
        <input class="form-control" name="website_url" value="<?= e($val('website_url','')) ?>" placeholder="https://">
      </div>
    </div>

    <!-- SEZIONE: INDIRIZZO -->
    <h6 class="text-uppercase text-muted mb-2">Indirizzo</h6>
    <div class="row g-2 mb-3">
      <div class="col-md-5">
        <label class="form-label">Via</label>
        <input class="form-control" name="street" value="<?= e($val('street','')) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">N. Civico</label>
        <input class="form-control" name="street_no" value="<?= e($val('street_no','')) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">CAP</label>
        <input class="form-control" name="cap" value="<?= e($val('cap','')) ?>" placeholder="00000">
      </div>
      <div class="col-md-2">
        <label class="form-label">Località</label>
        <input class="form-control" name="city" value="<?= e($val('city','')) ?>">
      </div>
      <div class="col-md-1">
        <label class="form-label">Prov.</label>
        <input class="form-control" name="province" value="<?= e($val('province','')) ?>" placeholder="VV">
      </div>
    </div>

    <!-- SEZIONE: INFO COMMERCIALI -->
    <h6 class="text-uppercase text-muted mb-2">Info commerciali</h6>
    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <label class="form-label">RCO</label>
        <select class="form-select" name="rco_user_id">
          <option value="">-- seleziona --</option>
          <?php foreach ($users as $u): ?>
            <?php $sel = ((int)$val('rco_user_id',0) === (int)$u['id']); ?>
            <option value="<?= (int)$u['id'] ?>" <?= $sel ? 'selected' : '' ?>>
              <?= e($u['last_name'].' '.$u['first_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Segnalata da</label>
        <select class="form-select" name="referred_by_user_id">
          <option value="">-- seleziona --</option>
          <?php foreach ($users as $u): ?>
            <?php $sel = ((int)$val('referred_by_user_id',0) === (int)$u['id']); ?>
            <option value="<?= (int)$u['id'] ?>" <?= $sel ? 'selected' : '' ?>>
              <?= e($u['last_name'].' '.$u['first_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Categoria</label>
