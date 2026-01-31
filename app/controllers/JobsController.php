<?php
require __DIR__ . '/../services/NotificationService.php';

class JobsController {

  // ... list(), view(), planning(), actual() ecc.

  public static function view() {
    $id = (int)($_GET['id'] ?? 0);

    $st = db()->prepare("SELECT j.*, c.business_name, o.protocol_full AS offer_protocol
                         FROM jobs j
                         JOIN companies c ON c.id=j.company_id
                         JOIN offers o ON o.id=j.offer_id
                         WHERE j.id=? LIMIT 1");
    $st->execute([$id]);
    $job = $st->fetch();
    if (!$job) { flash('error','Commessa non trovata'); redirect('/jobs'); }

    $u = current_user();
    $canAssign = self::isAreaManager($u);

    $consultants = self::consultantsList(); // utenti selezionabili

    require __DIR__ . '/../../views/jobs/view.php';
  }

  public static function assign() {
    csrf_verify();

    $jobId = (int)($_POST['job_id'] ?? 0);
    $assignedUserId = (int)($_POST['assigned_user_id'] ?? 0);
    $areaCode = $_POST['area_code'] ?? null;

    $u = current_user();
    if (!self::isAreaManager($u)) {
      http_response_code(403);
      exit("Non autorizzato");
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
      // lock commessa
      $st = $pdo->prepare("SELECT j.*, c.business_name
                           FROM jobs j
                           JOIN companies c ON c.id=j.company_id
                           WHERE j.id=? FOR UPDATE");
      $st->execute([$jobId]);
      $job = $st->fetch();
      if (!$job) throw new RuntimeException("Commessa non trovata");

      // update assegnazione
      $upd = $pdo->prepare("UPDATE jobs
                            SET assigned_user_id=?,
                                assigned_at=NOW(),
                                assigned_by_user_id=?,
                                area_code=?
                            WHERE id=?");
      $upd->execute([$assignedUserId, (int)$u['id'], $areaCode, $jobId]);

      // notifica al consulente
      $title = "Commessa assegnata";
      $msg = "Ti è stata assegnata una commessa.\nCliente: ".$job['business_name']."\nCommessa ID: ".$jobId;
      $link = "/jobs/view?id=".$jobId;

      NotificationService::notify($pdo, $assignedUserId, $title, $msg, $link);

      $pdo->commit();
      flash('success', 'Commessa assegnata e notifica inviata.');
      redirect("/jobs/view?id=$jobId");

    } catch (Throwable $e) {
      $pdo->rollBack();
      flash('error', 'Errore assegnazione: '.$e->getMessage());
      redirect("/jobs/view?id=$jobId");
    }
  }

  // ------- Helpers (puoi sostituire con ruoli DB) -------

  private static function isAreaManager(array $u): bool {
    $name = mb_strtolower(trim((string)($u['name'] ?? '')));
    // match semplici sui tre responsabili area
    return str_contains($name, 'giovanni di tommaso')
        || str_contains($name, 'francesco iannello')
        || str_contains($name, 'paola cutruzzul');
  }

  private static function consultantsList(): array {
    // Se preferisci: filtra per ruolo o per elenco codici.
    // Qui prendo tutti gli attivi e poi potrai restringere.
    $rows = db()->query("SELECT id, first_name, last_name FROM users WHERE is_active=1 ORDER BY last_name, first_name")->fetchAll();

    // Opzionale: restringi a soli cognomi/nominativi previsti
    $allowed = [
      'giovanni di tommaso',
      'salvatore marzano',
      'francesco iannello',
      'paola cutruzzul',
      'domenico pronesti',
      'francesco di bella'
    ];

    $out = [];
    foreach ($rows as $r) {
      $full = mb_strtolower(trim($r['first_name'].' '.$r['last_name']));
      foreach ($allowed as $a) {
        if (str_contains($full, $a)) { $out[] = $r; break; }
      }
    }
    // se non trova nulla (nomi non combaciano), fallback a tutti
    return $out ?: $rows;
  }
}
