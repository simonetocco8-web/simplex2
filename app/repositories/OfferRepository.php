<?php

class OfferRepository {

  public static function getCompanies(): array {
    return db()->query("SELECT id, business_name FROM companies ORDER BY business_name")->fetchAll();
  }

  public static function getUsers(): array {
    return db()->query("SELECT id, first_name, last_name FROM users WHERE is_active=1 ORDER BY last_name, first_name")->fetchAll();
  }

  public static function getPromotersCompanies(): array {
    $sql = "SELECT c.id, c.business_name
            FROM companies c
            WHERE EXISTS (
              SELECT 1 FROM company_type_map m
              JOIN company_types t ON t.id=m.company_type_id
              WHERE m.company_id=c.id AND t.name='Promotore'
            )
            ORDER BY c.business_name";
    return db()->query($sql)->fetchAll();
  }

  public static function find(int $id): ?array {
    $sql = "SELECT o.*, c.business_name
            FROM offers o
            JOIN companies c ON c.id=o.company_id
            WHERE o.id=? LIMIT 1";
    $st = db()->prepare($sql);
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function getPhases(int $offerId): array {
    $st = db()->prepare("SELECT * FROM offer_work_phases WHERE offer_id=? ORDER BY id ASC");
    $st->execute([$offerId]);
    return $st->fetchAll();
  }

  // -------- LIST (filtri + paginazione) --------
  public static function countFull(array $f): int {
    [$where, $params] = self::buildWhere($f);
    $sql = "SELECT COUNT(*) FROM offers o
            JOIN companies c ON c.id=o.company_id
            $where";
    $st = db()->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
  }

  public static function searchFull(array $f, int $limit, int $offset): array {
    [$where, $params] = self::buildWhere($f);

    $sql = "SELECT o.*, c.business_name,
              u.first_name AS rco_fn, u.last_name AS rco_ln
            FROM offers o
            JOIN companies c ON c.id=o.company_id
            LEFT JOIN users u ON u.id=o.rco_user_id
            $where
            ORDER BY o.protocol_year DESC, o.protocol_no DESC
            LIMIT $limit OFFSET $offset";

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }

  private static function buildWhere(array $f): array {
    $w = "WHERE 1=1";
    $p = [];

    $addLike = function($key, $col) use (&$w, &$p, $f) {
      if (isset($f[$key]) && trim((string)$f[$key]) !== '') {
        $w .= " AND $col LIKE ?";
        $p[] = '%'.trim((string)$f[$key]).'%';
      }
    };
    $addEq = function($key, $col) use (&$w, &$p, $f) {
      if (isset($f[$key]) && trim((string)$f[$key]) !== '') {
        $w .= " AND $col = ?";
        $p[] = trim((string)$f[$key]);
      }
    };

    $addLike('protocol', 'o.protocol_full');
    $addLike('business_name', 'c.business_name');
    $addEq('service', 'o.service');
    $addEq('status', 'o.status');
    $addEq('priority', 'o.priority');

    if (!empty($f['company_id'])) { $w.=" AND o.company_id=?"; $p[]=(int)$f['company_id']; }
    if (!empty($f['rco_user_id'])) { $w.=" AND o.rco_user_id=?"; $p[]=(int)$f['rco_user_id']; }
    if (!empty($f['promoter_company_id'])) { $w.=" AND o.promoter_company_id=?"; $p[]=(int)$f['promoter_company_id']; }

    // date range offerta
    if (!empty($f['offer_date_from'])) { $w.=" AND o.offer_date >= ?"; $p[]=$f['offer_date_from']; }
    if (!empty($f['offer_date_to'])) { $w.=" AND o.offer_date <= ?"; $p[]=$f['offer_date_to']; }

    // scadenza range
    if (!empty($f['expiry_from'])) { $w.=" AND o.expiry_date >= ?"; $p[]=$f['expiry_from']; }
    if (!empty($f['expiry_to'])) { $w.=" AND o.expiry_date <= ?"; $p[]=$f['expiry_to']; }

    // subservice/specific_service search
    $addLike('subservice', 'o.subservice');
    $addLike('specific_service', 'o.specific_service');

    return [$w, $p];
  }

  // -------- SAVE (offerta + fasi) --------
  public static function saveOfferWithPhases(array $offer, array $phases, ?int $id = null): int {
    $pdo = db();
    $pdo->beginTransaction();
    try {
      if ($id) {
        $sql = "UPDATE offers SET
          company_id=?, service=?, subservice=?, specific_service=?,
          object_specs=?, service_location=?,
          rco_user_id=?, referred_by_user_id=?,
          priority=?, offer_date=?, validity_days=?, expiry_date=?,
          notes=?, promoter_company_id=?, promoter_commission_type=?, promoter_commission_value=?,
          payment_terms=?, discount_pct=?,
          updated_at=NOW()
          WHERE id=?";
        $pdo->prepare($sql)->execute([
          $offer['company_id'], $offer['service'], $offer['subservice'], $offer['specific_service'],
          $offer['object_specs'], $offer['service_location'],
          $offer['rco_user_id'], $offer['referred_by_user_id'],
          $offer['priority'], $offer['offer_date'], $offer['validity_days'], $offer['expiry_date'],
          $offer['notes'], $offer['promoter_company_id'], $offer['promoter_commission_type'], $offer['promoter_commission_value'],
          $offer['payment_terms'], $offer['discount_pct'],
          $id
        ]);
        $offerId = $id;
      } else {
        // protocollo progressivo
        $year = (int)date('Y');
        $no = ProtocolService::next('offer', $year, $pdo);
        $protocol = ProtocolService::formatOffer($no, $year);

        $sql = "INSERT INTO offers
          (protocol_no, protocol_year, protocol_full,
           company_id, service, subservice, specific_service,
           object_specs, service_location,
           rco_user_id, referred_by_user_id,
           priority, offer_date, validity_days, expiry_date,
           notes, promoter_company_id, promoter_commission_type, promoter_commission_value,
           payment_terms, discount_pct, status)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $pdo->prepare($sql)->execute([
          $no, $year, $protocol,
          $offer['company_id'], $offer['service'], $offer['subservice'], $offer['specific_service'],
          $offer['object_specs'], $offer['service_location'],
          $offer['rco_user_id'], $offer['referred_by_user_id'],
          $offer['priority'], $offer['offer_date'], $offer['validity_days'], $offer['expiry_date'],
          $offer['notes'], $offer['promoter_company_id'], $offer['promoter_commission_type'], $offer['promoter_commission_value'],
          $offer['payment_terms'], $offer['discount_pct'],
          'bozza'
        ]);
        $offerId = (int)$pdo->lastInsertId();
      }

      // riscrivi fasi (semplice e affidabile)
      $pdo->prepare("DELETE FROM offer_work_phases WHERE offer_id=?")->execute([$offerId]);
      if ($phases) {
        $ins = $pdo->prepare("INSERT INTO offer_work_phases
          (offer_id, phase_type, amount_eur, planned_date, is_billable, day_man_value_eur, hours, days)
          VALUES (?,?,?,?,?,?,?,?)");
        foreach ($phases as $ph) {
          $ins->execute([
            $offerId,
            $ph['phase_type'],
            $ph['amount_eur'],
            $ph['planned_date'],
            $ph['is_billable'],
            $ph['day_man_value_eur'],
            $ph['hours'],
            $ph['days'],
          ]);
        }
      }

      $pdo->commit();
      return $offerId;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function delete(int $id): void {
    $pdo = db();
    $pdo->beginTransaction();
    try {
      $pdo->prepare("DELETE FROM offers WHERE id=?")->execute([$id]);
      // fasi ON DELETE CASCADE se FK, altrimenti già ripulite
      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
