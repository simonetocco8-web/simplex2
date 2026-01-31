<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="m-0"><?= e($company['business_name']) ?></h4>
    <div class="text-muted small">P.IVA: <?= e($company['vat_number']) ?><?= $company['tax_code'] ? ' · CF: '.e($company['tax_code']) : '' ?></div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="/companies">← Elenco</a>
    <a class="btn btn-outline-primary" href="/companies/new?id=<?= (int)$company['id'] ?>">Modifica</a>
    <form method="POST" action="/companies/delete" onsubmit="return confirm('Eliminare azienda?');">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$company['id'] ?>">
      <button class="btn btn-outline-danger">Elimina</button>
    </form>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Dati</h6>
        <div><strong>IBAN:</strong> <?= e($company['iban'] ?? '') ?></div>
        <div><strong>Codice fatturazione:</strong> <?= e($company['invoicing_code'] ?? '') ?></div>
        <div><strong>Tipologie:</strong> <?= e(implode(', ', $typeNames) ?: '-') ?></div>
        <hr>
        <div><strong>Organico medio:</strong> <?= e($company['staff_avg_range']) ?></div>
        <div><strong>Fatturato €:</strong> <?= e(number_format((float)$company['turnover_eur'], 2, ',', '.')) ?></div>
        <div><strong>Categoria:</strong> <?= e($company['category'] ?? '-') ?></div>
        <div><strong>EA:</strong> <?= e($company['ea_code'] ?? '-') ?></div>
        <div><strong>Conosciuto come:</strong> <?= e($company['known_as'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Contatti & indirizzo</h6>
        <div><strong>Telefono:</strong> <?= e($company['phone'] ?? '-') ?></div>
        <div><strong>Email:</strong> <?= e($company['email'] ?? '-') ?></div>
        <div><strong>PEC:</strong> <?= e($company['pec_email'] ?? '-') ?></div>
        <div><strong>Sito:</strong> <?= e($company['website_url'] ?? '-') ?></div>
        <hr>
        <div><strong>Indirizzo:</strong>
          <?= e(trim(($company['street'] ?? '').' '.($company['street_no'] ?? ''))) ?>,
          <?= e($company['cap'] ?? '') ?> <?= e($company['city'] ?? '') ?> (<?= e($company['province'] ?? '') ?>)
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted">Testi</h6>
        <div class="row">
          <div class="col-md-6">
            <div class="text-muted small">Principali Prodotti</div>
            <div class="border rounded p-2 bg-light" style="white-space:pre-wrap;"><?= e($company['main_products'] ?? '') ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Note</div>
            <div class="border rounded p-2 bg-light" style="white-space:pre-wrap;"><?= e($c
