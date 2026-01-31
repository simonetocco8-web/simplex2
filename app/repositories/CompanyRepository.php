<?php

class CompanyRepository {
    
    
    
    public static function searchFull(array $f, int $limit, int $offset): array {
    [$whereSql, $params, $joinsSql] = self::buildCompanyWhere($f);

    $sql = "SELECT c.*,
              u1.first_name AS rco_fn, u1.last_name AS rco_ln,
              u2.first_name AS ref_fn, u2.last_name AS ref_ln,
              p.business_name AS promoter_name
            FROM companies c
            $joinsSql
            LEFT JOIN users u1 ON u1.id = c.rco_user_id
            LEFT JOIN users u2 ON u2.id = c.referred_by_user_id
            LEFT JOIN companies p ON p.id = c.promoter_company_id
            $whereSql
            ORDER BY c.business_name ASC
            LIMIT $limit OFFSET $offset";

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }

  public static function countFull(array $f): int {
    [$whereSql, $params, $joinsSql] = self::buildCompanyWhere($f);

    $sql = "SELECT COUNT(DISTINCT c.id)
            FROM companies c
            $joinsSql
            $whereSql";

    $st = db()->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
  }

  /**
   * Costruisce WHERE + JOIN necessari per filtri su tutti i campi.
   * Ritorna: [whereSql, params, joinsSql]
   */
  private static function buildCompanyWhere(array $f): array {
    $where = "WHERE 1=1";
    $params = [];
    $joins = "";

    // Helper
    $addLike = function(string $key, string $col) use (&$where, &$params, $f) {
      if (isset($f[$key]) && trim((string)$f[$key]) !== '') {
        $where .= " AND $col LIKE ?";
        $params[] = '%'.trim((string)$f[$key]).'%';
      }
    };
    $addEq = function(string $key, string $col) use (&$where, &$params, $f) {
      if (isset($f[$key]) && trim((string)$f[$key]) !== '') {
        $where .= " AND $col = ?";
        $params[] = trim((string)$f[$key]);
      }
    };

    // --- Filtri testo ---
    $addLike('vat_number', 'c.vat_number');
    $addLike('tax_code', 'c.tax_code');
    $addLike('business_name', 'c.business_name');
    $addLike('iban', 'c.iban');

    $addLike('invoicing_code', 'c.invoicing_code');
    $addLike('phone', 'c.phone');
    $addLike('pec_email', 'c.pec_email');
    $addLike('email', 'c.email');
    $addLike('website_url', 'c.website_url');

    $addLike('street', 'c.street');
    $addLike('street_no', 'c.street_no');
    $addLike('cap', 'c.cap');
    $addLike('city', 'c.city');
    $addLike('province', 'c.province');

    // --- Select / FK ---
    // RCO / Segnalata da
    if (!empty($f['rco_user_id'])) {
      $where .= " AND c.rco_user_id = ?";
      $params[] = (int)$f['rco_user_id'];
    }
    if (!empty($f['referred_by_user_id'])) {
      $where .= " AND c.referred_by_user_id = ?";
      $params[] = (int)$f['referred_by_user_id'];
    }

    // Categoria, EA, Conosciuto come
    $addEq('category', 'c.category');
    $addEq('ea_code', 'c.ea_code');
    $addEq('known_as', 'c.known_as');

    // Promotore (azienda)
    if (!empty($f['promoter_company_id'])) {
      $where .= " AND c.promoter_company_id = ?";
      $params[] = (int)$f['promoter_company_id'];
    }

    // Organico medio (range) -> LIKE per permettere anche ricerche parziali
    $addLike('staff_avg_range', 'c.staff_avg_range');

    // Fatturato range
    if (isset($f['turnover_min']) && trim((string)$f['turnover_min']) !== '') {
      $min = self::toFloatEur($f['turnover_min']);
      $where .= " AND c.turnover_eur >= ?";
      $params[] = $min;
    }
    if (isset($f['turnover_max']) && trim((string)$f['turnover_max']) !== '') {
      $max = self::toFloatEur($f['turnover_max']);
      $where .= " AND c.turnover_eur <= ?";
      $params[] = $max;
    }

    // Note / Prodotti (testo libero)
    $addLike('main_products', 'c.main_products');
    $addLike('notes', 'c.notes');

    // --- Tipologia azienda (company_type_map) ---
    if (!empty($f['type'])) {
      // join solo se serve (più efficiente)
      $joins .= " JOIN company_type_map m ON m.company_id = c.id
                 JOIN company_types t ON t.id = m.company_type_id ";
      $where .= " AND t.name = ?";
      $params[] = trim((string)$f['type']);
    }

    return [$where, $params, $joins];
  }

  private static function toFloatEur($raw): float {
    $s = trim((string)$raw);
    $s = str_replace([' ', '.'], '', $s);
    $s = str_replace(',', '.', $s);
    return (float)$s;
  }



  public static function getTypes(): array {
    $st = db()->query("SELECT id, name FROM company_types ORDER BY name");
    return $st->fetchAll();
  }

  public static function getUsers(): array {
    $st = db()->query("SELECT id, first_name, last_name FROM users WHERE is_active=1 ORDER BY last_name, first_name");
    return $st->fetchAll();
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
    $st = db()->prepare("SELECT * FROM companies WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function getTypeIdsForCompany(int $companyId): array {
    $st = db()->prepare("SELECT company_type_id FROM company_type_map WHERE company_id=?");
    $st->execute([$companyId]);
    return array_map(fn($r) => (int)$r['company_type_id'], $st->fetchAll());
  }

  public static function delete(int $id): void {
    $pdo = db();
    $pdo->beginTransaction();
    try {
      $pdo->prepare("DELETE FROM companies WHERE id=?")->execute([$id]);
      // mapping ON DELETE CASCADE, altrimenti:
      // $pdo->prepare("DELETE FROM company_type_map WHERE company_id=?")->execute([$id]);
      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  /**
   * Upsert azienda + tipologie (in transazione).
   * @return int company_id
   */
  public static function save(array $data, array $typeIds, ?int $id = null): int {
    $pdo = db();
    $pdo->beginTransaction();
    try {
      if ($id) {
        $sql = "UPDATE companies SET
          vat_number=?,
          tax_code=?,
          business_name=?,
          iban=?,
          invoicing_code=?,
          phone=?,
          pec_email=?,
          email=?,
          website_url=?,
          street=?,
          street_no=?,
          cap=?,
          city=?,
          province=?,
          rco_user_id=?,
          referred_by_user_id=?,
          category=?,
          ea_code=?,
          staff_avg_range=?,
          turnover_eur=?,
          known_as=?,
          main_products=?,
          notes=?,
          promoter_company_id=?
          WHERE id=?";
        $pdo->prepare($sql)->execute([
          $data['vat_number'],
          $data['tax_code'],
          $data['business_name'],
          $data['iban'],
          $data['invoicing_code'],
          $data['phone'],
          $data['pec_email'],
          $data['email'],
          $data['website_url'],
          $data['street'],
          $data['street_no'],
          $data['cap'],
          $data['city'],
          $data['province'],
          $data['rco_user_id'],
          $data['referred_by_user_id'],
          $data['category'],
          $data['ea_code'],
          $data['staff_avg_range'],
          $data['turnover_eur'],
          $data['known_as'],
          $data['main_products'],
          $data['notes'],
          $data['promoter_company_id'],
          $id
        ]);
        $companyId = $id;
      } else {
        $sql = "INSERT INTO companies
          (vat_number,tax_code,business_name,iban,invoicing_code,phone,pec_email,email,website_url,
           street,street_no,cap,city,province,
           rco_user_id,referred_by_user_id,category,ea_code,staff_avg_range,turnover_eur,known_as,
           main_products,notes,promoter_company_id)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([
          $data['vat_number'],
          $data['tax_code'],
          $data['business_name'],
          $data['iban'],
          $data['invoicing_code'],
          $data['phone'],
          $data['pec_email'],
          $data['email'],
          $data['website_url'],
          $data['street'],
          $data['street_no'],
          $data['cap'],
          $data['city'],
          $data['province'],
          $data['rco_user_id'],
          $data['referred_by_user_id'],
          $data['category'],
          $data['ea_code'],
          $data['staff_avg_range'],
          $data['turnover_eur'],
          $data['known_as'],
          $data['main_products'],
          $data['notes'],
          $data['promoter_company_id'],
        ]);
        $companyId = (int)$pdo->lastInsertId();
      }

      // aggiorna tipologie
      $pdo->prepare("DELETE FROM company_type_map WHERE company_id=?")->execute([$companyId]);
      if ($typeIds) {
        $ins = $pdo->prepare("INSERT INTO company_type_map(company_id, company_type_id) VALUES (?,?)");
        foreach ($typeIds as $tid) $ins->execute([$companyId, (int)$tid]);
      }

      $pdo->commit();
      return $companyId;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
