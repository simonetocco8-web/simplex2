<?php require __DIR__ . '/../layout/header.php'; ?>
<?php require __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Helper per mantenere querystring in paginazione
function qs(array $override = []): string {
  $q = array_merge($_GET, $override);
  // rimuovi valori vuoti per URL più puliti
  foreach ($q as $k => $v) if ($v === '' || $v === null) unset($q[$k]);
  return http_build_query($q);
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Aziende</h4>
  <a class="btn btn-primary" href="/companies/new">+ Nuova Azienda</a>
</div>

<form class="card card-body mb-3" method="GET" action="/companies">
  <div class="row g-2">

    <!-- Anagrafica -->
    <div class="col-md-2">
      <input class="form-control" name="vat_number" placeholder="P.IVA"
             value="<?= e($_GET['vat_number'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input class="form-control" name="tax_code" placeholder="Cod. Fiscale"
             value="<?= e($_GET['tax_code'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <input class="form-control" name="business_name" placeholder="Ragione Sociale"
             value="<?= e($_GET['business_name'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input class="form-control" name="iban" placeholder="IBAN"
             value="<?= e($_GET['iban'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <select class="form-select" name="type">
        <option value="">Tipologia (tutte)</option>
        <?php foreach ($typesSimple as $t): ?>
          <option <?= (($_GET['type'] ?? '')===$t)?'selected':'' ?> value="<?= e($t) ?>"><?= e($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Contatti -->
    <div class="col-md-2">
      <input class="form-control" name="invoicing_code" placeholder="Cod. fatturazione"
             value="<?= e($_GET['invoicing_code'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input class="form-control" name="phone" placeholder="Telefono"
             value="<?= e($_GET['phone'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input class="form-control" name="pec_email" placeholder="PEC"
             value="<?= e($_GET['pec_email'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input class="form-control" name="email" placeholder="Email"
             value="<?= e($_GET['email'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input class="form-control" name="website_url" placeholder="URL sito"
             value="<?= e($_GET['website_url'] ?? '') ?>">
    </div>

    <!-- Indirizzo -->
    <div class="col-md-3">
      <input class="form-control" name="street" placeholder="Via"
             value="<?= e($_GET['street'] ?? '') ?>">
    </div>
    <div class="col-md-1">
      <input class="form-control" name="street_no" placeholder="N°"
             value="<?= e($_GET['street_no'] ?? '') ?>">
    </div>
    <div class="col-md-1">
      <input class="form-control" name="cap" placeholder="CAP"
             value="<?= e($_GET['cap'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input class="form-control" name="city" placeholder="Località"
             value="<?= e($_GET['city'] ?? '') ?>">
    </div>
    <div class="col-md-1">
      <input class="form-control" name="province" placeholder="Prov."
             value="<?= e($_GET['province'] ?? '') ?>">
    </div>

    <!-- Info commerciali -->
    <div class="col-md-3">
      <select class="form-select" name="rco_user_id">
        <option value="">RCO (tutti)</option>
        <?php foreach ($users as $u): ?>
          <?php $id = (int)$u['id']; ?>
          <option value="<?= $id ?>" <?= ((int)($_GET['rco_user_id'] ?? 0) === $id)?'selected':'' ?>>
            <?= e($u['last_name'].' '.$u['first_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select class="form-select" name="referred_by_user_id">
        <option value="">Segnalata da (tutti)</option>
        <?php foreach ($users as $u): ?>
          <?php $id = (int)$u['id']; ?>
          <option value="<?= $id ?>" <?= ((int)($_GET['referred_by_user_id'] ?? 0) === $id)?'selected':'' ?>>
            <?= e($u['last_name'].' '.$u['first_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select class="form-select" name="category">
        <option value="">Categoria (tutte)</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c) ?>" <?= (($_GET['category'] ?? '')===$c)?'selected':'' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select class="form-select" name="ea_code">
        <option value="">EA (tutti)</option>
        <?php foreach ($eaCodes as $ea): ?>
          <option value="<?= e($ea) ?>" <?= (($_GET['ea_code'] ?? '')===$ea)?'selected':'' ?>><?= e($ea) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <input class="form-control" name="staff_avg_range" placeholder="Organico medio (es 1-5)"
             value="<?= e($_GET['staff_avg_range'] ?? '') ?>">
    </div>

    <div class="col-md-2">
      <input class="form-control" name="turnover_min" placeholder="Fatturato min €"
             value="<?= e($_GET['turnover_min'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input class="form-control" name="turnover_max" placeholder="Fatturato max €"
             value="<?= e($_GET['turnover_max'] ?? '') ?>">
    </div>

    <div class="col-md-3">
      <select class="form-select" name="known_as">
        <option value="">Conosciuto come (tutti)</option>
        <?php foreach ($knownAs as $k): ?>
          <option value="<?= e($k) ?>" <?= (($_GET['known_as'] ?? '')===$k)?'selected':'' ?>><?= e($k) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select class="form-select" name="promoter_company_id">
        <option value="">Promotore (tutti)</option>
        <?php foreach ($promoters as $p): ?>
          <option value="<?= (int)$p['id'] ?>" <?= ((int)($_GET['promoter_company_id'] ?? 0) === (int)$p['id'])?'selected':'' ?>>
            <?= e($p['business_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Testo libero -->
    <div class="col-md-3">
      <input class="form-control" name="main_products" placeholder="Cerca nei prodotti"
             value="<?= e($_GET['main_products'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input class="form-control" name="notes" placeholder="Cerca nelle note"
             value="<?= e($_GET['notes'] ?? '') ?>">
    </div>

    <!-- Bottoni -->
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-outline-primary w-100">Filtra</button>
      <a class="btn btn-outline-secondary w-100" href="/companies">Reset</a>
    </div>

  </div>
</form>

<div class="d-flex justify-content-between align-items-center mb-2">
  <div class="text-muted small">
    Risultati: <strong><?= (int)$total ?></strong>
    <?php if ($pages > 1): ?> · Pagina <strong><?= (int)$page ?></strong> / <?= (int)$pages ?><?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr>
          <th>Ragione Sociale</th>
          <th>P.IVA</th>
          <th>Categoria</th>
          <th>RCO</th>
          <th>Località</th>
          <th class="text-end">Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="fw-semibold"><?= e($r['business_name']) ?></td>
            <td><?= e($r['vat_number']) ?></td>
            <td><?= e($r['category'] ?? '-') ?></td>
            <td><?= e(trim(($r['rco_ln'] ?? '').' '.($r['rco_fn'] ?? '')) ?: '-') ?></td>
            <td><?= e(($r['city'] ?? '').' '.($r['province'] ?? '')) ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="/companies/view?id=<?= (int)$r['id'] ?>">Vedi</a>
              <a class="btn btn-sm btn-outline-secondary" href="/companies/new?id=<?= (int)$r['id'] ?>">Modifica</a>
              <form class="d-inline" method="POST" action="/companies/delete" onsubmit="return confirm('Eliminare azienda?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Elimina</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Nessun risultato</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($pages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <li class="page-item <?= $page<=1 ? 'disabled':'' ?>">
        <a class="page-link" href="/companies?<?= e(qs(['page'=>max(1,$page-1)])) ?>">«</a>
      </li>
      <?php
        // Mostra finestrella pagine
        $start = max(1, $page - 2);
        $end = min($pages, $page + 2);
      ?>
      <?php for ($i=$start; $i<=$end; $i++): ?>
        <li class="page-item <?= $i===$page ? 'active':'' ?>">
          <a class="page-link" href="/companies?<?= e(qs(['page'=>$i])) ?>"><?= (int)$i ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= $page>=$pages ? 'disabled':'' ?>">
        <a class="page-link" href="/companies?<?= e(qs(['page'=>min($pages,$page+1)])) ?>">»</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
