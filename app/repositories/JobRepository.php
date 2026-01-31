<?php
class JobRepository {
  public static function search(array $f): array {
    $sql = "SELECT j.*, c.business_name, o.protocol_full AS offer_protocol,
              u.first_name AS ass_fn, u.last_name AS ass_ln
            FROM jobs j
            JOIN companies c ON c.id=j.company_id
            JOIN offers o ON o.id=j.offer_id
            LEFT JOIN users u ON u.id=j.assigned_user_id
            WHERE 1=1";
    $p = [];

    if (!empty($f['protocol'])) { $sql .= " AND j.protocol_full LIKE ?"; $p[] = '%'.$f['protocol'].'%'; }
    if (!empty($f['job_code'])) { $sql .= " AND j.job_code LIKE ?"; $p[] = '%'.$f['job_code'].'%'; }
    if (!empty($f['business_name'])) { $sql .= " AND c.business_name LIKE ?"; $p[] = '%'.$f['business_name'].'%'; }
    if (!empty($f['status'])) { $sql .= " AND j.progress_status = ?"; $p[] = $f['status']; }
    if (!empty($f['assigned_user_id'])) { $sql .= " AND j.assigned_user_id = ?"; $p[] = (int)$f['assigned_user_id']; }

    // budget range
    if ($f['budget_min'] !== '' && isset($f['budget_min'])) { $sql .= " AND j.budget_eur >= ?"; $p[] = (float)$f['budget_min']; }
    if ($f['budget_max'] !== '' && isset($f['budget_max'])) { $sql .= " AND j.budget_eur <= ?"; $p[] = (float)$f['budget_max']; }

    $sql .= " ORDER BY j.protocol_year DESC, j.protocol_no DESC LIMIT 200";

    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
  }

  public static function totalBudget(array $f): float {
    // stessa logica filtri, ma SUM su budget
    $sql = "SELECT COALESCE(SUM(j.budget_eur),0) AS tot
            FROM jobs j
            JOIN companies c ON c.id=j.company_id
            WHERE 1=1";
    $p = [];

    if (!empty($f['business_name'])) { $sql .= " AND c.business_name LIKE ?"; $p[] = '%'.$f['business_name'].'%'; }
    if (!empty($f['status'])) { $sql .= " AND j.progress_status = ?"; $p[] = $f['status']; }

    $st = db()->prepare($sql);
    $st->execute($p);
    return (float)$st->fetchColumn();
  }
}
