<?php
require __DIR__ . '/../repositories/OfferRepository.php';
require __DIR__ . '/../services/ProtocolService.php';

class OffersController {

  public static function list() {
    $filters = $_GET;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;
    $offset = ($page - 1) * $perPage;

    $total = OfferRepository::countFull($filters);
    $rows  = OfferRepository::searchFull($filters, $perPage, $offset);
    $pages = (int)ceil($total / $perPage);

    $companies = OfferRepository::getCompanies();
    $users = OfferRepository::getUsers();
    $promoters = OfferRepository::getPromotersCompanies();

    $services = self::services();
    $statuses = ['bozza','inviata','aggiudicata','annullata'];
    $priorities = ['alto','medio','basso'];

    require __DIR__ . '/../../views/offers/list.php';
  }

  public static function form() {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $offer = $id ? OfferRepository::find($id) : null;
    $phases = $id ? OfferRepository::getPhases($id) : [];

    $companies = OfferRepository::getCompanies();
    $users = OfferRepository::getUsers();
    $promoters = OfferRepository::getPromotersCompanies();

    $services = self::services();
    $subservicesSga = self::subservicesSGA();
    $specificSicurezza = self::specificSicurezza();
    $specificFormazione = self::specificFormazione();
    $specificFinanza = self::specificFinanza();
    $specificSOA = self::specificSOA();
    $specificAltre = self::specificAltre();

    $paymentTerms = self::paymentTerms();

    require __DIR__ . '/../../views/offers/form.php';
  }

  public static function view() {
    $id = (int)($_GET['id'] ?? 0);
    $offer = OfferRepository::find($id);
    if (!$offer) { flash('error','Offerta non trovata'); redirect('/offers'); }

    $phases = OfferRepository::getPhases($id);

    require __DIR__ . '/../../views/offers/view.php';
  }

  public static function save() {
    csrf_verify();
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

    [$offer, $phases] = self::sanitizePost($_POST);

    $errors = self::validate($offer, $phases);
    if ($errors) {
      foreach ($errors as $e) flash('error', $e);
      redirect($id ? "/offers/new?id=$id" : "/offers/new");
    }

    try {
      $offerId = OfferRepository::saveOfferWithPhases($offer, $phases, $id);
      flash('success', $id ? 'Offerta aggiornata' : 'Offerta creata');
      redirect("/offers/view?id=$offerId");
    } catch (Throwable $e) {
      flash('error', 'Errore salvataggio offerta: '.$e->getMessage());
      redirect($id ? "/offers/new?id=$id" : "/offers/new");
    }
  }

  public static function delete() {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    try {
      OfferRepository::delete($id);
      flash('success','Offerta eliminata');
    } catch (Throwable $e) {
      flash('error','Errore eliminazione: '.$e->getMessage());
    }
    redirect('/offers');
  }

  public static function history() {
    $id = (int)($_GET['id'] ?? 0);
    $offer = OfferRepository::find($id);
    if (!$offer) { flash('error','Offerta non trovata'); redirect('/offers'); }

    $st = db()->prepare("SELECT a.*, u.first_name, u.last_name
                         FROM audit_log a
                         JOIN users u ON u.id=a.changed_by
                         WHERE a.entity_type='offer' AND a.entity_id=?
                         ORDER BY a.changed_at DESC LIMIT 200");
    $st->execute([$id]);
    $logs = $st->fetchAll();

    require __DIR__ . '/../../views/offers/history.php';
  }

  /**
   * Cambio stato: se diventa aggiudicata => crea commessa.
   */
  public static function changeStatus() {
    csrf_verify();
    $offerId = (int)($_POST['id'] ?? 0);
    $new = $_POST['status'] ?? 'bozza';

    $pdo = db();
    $pdo->beginTransaction();
    try {
      // lock offerta
      $st = $pdo->prepare("SELECT * FROM offers WHERE id=? FOR UPDATE");
      $st->execute([$offerId]);
      $offer = $st->fetch();
      if (!$offer) throw new RuntimeException("Offerta non trovata");

      // aggiorna stato
      $pdo->prepare("UPDATE offers SET status=?, updated_at=NOW() WHERE id=?")->execute([$new, $offerId]);

      // audit
      self::audit($pdo, 'offer', $offerId, 'status_change', ['status'=>['old'=>$offer['status'],'new'=>$new]]);

      if ($new === 'aggiudicata') {
        // crea commessa
        $year = (int)date('Y');
        $jobNo = ProtocolService::next('job', $year, $pdo);
        $jobProtocol = ProtocolService::formatJob($jobNo, $year);

        $jobSeq = ProtocolService::next('jobcode', $year, $pdo);
        $jobCode = ProtocolService::formatJobCode($year, $jobSeq);

        $ins = $pdo->prepare("INSERT INTO jobs
          (protocol_no, protocol_year, protocol_full, job_code, company_id, offer_id, progress_status)
          VALUES (?,?,?,?,?,?,?)");
        $ins->execute([
          $jobNo, $year, $jobProtocol, $jobCode,
          (int)$offer['company_id'], (int)$offer['id'],
          'aperta'
        ]);
        $jobId = (int)$pdo->lastInsertId();

        self::audit($pdo, 'job', $jobId, 'create', ['from_offer'=>$offer['protocol_full']]);

        $pdo->commit();
        flash('success', "Offerta aggiudicata → Commessa creata: $jobProtocol");
        redirect("/jobs/view?id=$jobId");
      }

      $pdo->commit();
      flash('success', 'Stato offerta aggiornato');
      redirect("/offers/view?id=$offerId");

    } catch (Throwable $e) {
      $pdo->rollBack();
      flash('error', 'Errore cambio stato: '.$e->getMessage());
      redirect("/offers/view?id=$offerId");
    }
  }

  // ---------------- Lookups ----------------
  public static function services(): array {
    return [
      'SISTEMI DI GESTIONE AZIENDALE',
      'SICUREZZA',
      'FORMAZIONE',
      'FINANZA AGEVOLATA',
      'CONSULENZA SOA',
      'ALTRE CONSULENZE'
    ];
  }

  public static function subservicesSGA(): array {
    return [
      'ISO 9001','MANT ISO 9001','ISO 14001','MANT ISO 14001','EMAS','MANTENIMENTO EMAS',
      'ISO 45001','MANT ISO 45001','SA 8000','MANT SA8000','ISO 50000','MANT ISO 50000',
      'ISO 27001','MANT ISO 27001','ISO 27017','MANT ISO 27017','ISO 27018','MANT ISO 27018',
      'ISO 42000','MANT ISO 42000','ISO 37001','MANT ISO 37001','ISO 39001','MANT 39001',
      'ISO 22000','MANT ISO 22000','ISO 22005','MANT ISO 22005','ISO 1090','MANT ISO 1090',
      'SISTEMA INTEGRATO','MANTENIMENTO SISTEMA INTEGRATO',
      'HALAL','MANTENIMENTO HALAL','GLOBAL GAP','MANTENIMENTO GLOBAL GAP','BIOLOGICO','MANTENIMENTO BIOLOGICO',
      'MARC CE','FPC CLS','MANTENIMENTO FPC CLS','MANTENIMENTO MAR CE',
      'BRC','MANTENIMENTO BRC AEO','Parità di genere','MANTENIMENTO Parità di genere',
      'MODELLO 231','ODV 231','PRIVACY (GDPR)','HACCP','MANT HACCP','ANALISI TAMPONE HACCP'
    ];
  }

  public static function specificSicurezza(): array {
    return ['DVR (D. Lgs. 81/2008)','AGGIORNAMENTO DVR','MISURAZIONI TECNICHE','VISITE MEDICHE'];
  }
  public static function specificFormazione(): array {
    return ['FORMAZIONE D. Lgs. 81/2008','Formazione Profili ENEL','ALTRE ATTIVITA’ DI FORMAZIONE'];
  }
  public static function specificFinanza(): array {
    return ['REGIONE CALABRIA','INVITALIA','MINISTERO','ALTRO'];
  }
  public static function specificSOA(): array {
    return ['NUOVA ATTESTAZIONE','VERIFICA TRIENNALE','VERIFICA QUINQUENNALE','VARIAZIONE','ALTRO'];
  }
  public static function specificAltre(): array {
    return ['MARKETING','PIANI STRATEGICI','CONTROLLO DI GESTIONE','ALTRO'];
  }

  public static function paymentTerms(): array {
    return ['30-60 FMDF','Bonifico Bancario f.m.v.f','da definire','RI.BA.','RID','rid 06 mensilità','rimessa diretta','Rimessa Diretta'];
  }

  // ---------------- Sanitizzazione + Validazione ----------------
  private static function sanitizePost(array $p): array {
    $trim = fn($k) => isset($p[$k]) ? trim((string)$p[$k]) : null;
    $nul = fn($v) => ($v === null || $v === '') ? null : $v;

    $discount = $trim('discount_pct');
    $discount = ($discount === null || $discount === '') ? null : (int)$discount;

    $validity = (int)($p['validity_days'] ?? 0);

    // commissione
    $commType = $nul($trim('promoter_commission_type'));
    $commValRaw = $trim('promoter_commission_value');
    $commVal = null;
    if ($commValRaw !== null && $commValRaw !== '') {
      $s = str_replace([' ', '.'], '', $commValRaw);
      $s = str_replace(',', '.', $s);
      $commVal = (float)$s;
    }

    $offer = [
      'company_id' => (int)$p['company_id'],
      'service' => $trim('service'),
      'subservice' => $nul($trim('subservice')),
      'specific_service' => $nul($trim('specific_service')),
      'object_specs' => $nul($trim('object_specs')),
      'service_location' => $nul($trim('service_location')),
      'rco_user_id' => (int)$p['rco_user_id'],
      'referred_by_user_id' => !empty($p['referred_by_user_id']) ? (int)$p['referred_by_user_id'] : null,
      'priority' => $trim('priority'),
      'offer_date' => $nul($trim('offer_date')),
      'validity_days' => $validity,
      'expiry_date' => $nul($trim('expiry_date')),
      'notes' => $nul($trim('notes')),
      'promoter_company_id' => !empty($p['promoter_company_id']) ? (int)$p['promoter_company_id'] : null,
      'promoter_commission_type' => $commType,
      'promoter_commission_value' => $commVal,
      'payment_terms' => $nul($trim('payment_terms')),
      'discount_pct' => $discount,
    ];

    // fasi
    $phases = [];
    $rows = $p['phase'] ?? [];
    foreach ($rows as $row) {
      // skip righe vuote
      if (trim((string)($row['amount_eur'] ?? '')) === '' && trim((string)($row['planned_date'] ?? '')) === '') continue;

      $amtRaw = trim(
