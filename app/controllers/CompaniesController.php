<?php
require __DIR__ . '/../repositories/CompanyRepository.php';

class CompaniesController {

public static function list() {
    $filters = $_GET;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;
    $offset = ($page - 1) * $perPage;

    $total = CompanyRepository::countFull($filters);
    $rows = CompanyRepository::searchFull($filters, $perPage, $offset);

    // per i dropdown filtri
    $users = CompanyRepository::getUsers();
    $promoters = CompanyRepository::getPromotersCompanies();
    $categories = self::categories();
    $eaCodes = self::eaCodes();
    $knownAs = self::knownAs();
    $typesSimple = ['Potenziale Cliente','Promotore','Fornitore','Partner'];

    $pages = (int)ceil($total / $perPage);

    require __DIR__ . '/../../views/companies/list.php';
  }

  public static function form() {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $company = $id ? CompanyRepository::find($id) : null;
    $selectedTypeIds = $id ? CompanyRepository::getTypeIdsForCompany($id) : [];

    $types = CompanyRepository::getTypes();
    $users = CompanyRepository::getUsers();
    $promoters = CompanyRepository::getPromotersCompanies();

    // lookup arrays per select
    $categories = self::categories();
    $eaCodes = self::eaCodes();
    $knownAs = self::knownAs();

    require __DIR__ . '/../../views/companies/form.php';
  }

  public static function view() {
    $id = (int)($_GET['id'] ?? 0);
    $company = CompanyRepository::find($id);
    if (!$company) { flash('error','Azienda non trovata'); redirect('/companies'); }

    $typeIds = CompanyRepository::getTypeIdsForCompany($id);
    $typesAll = CompanyRepository::getTypes();
    $typeNames = [];
    foreach ($typesAll as $t) if (in_array((int)$t['id'], $typeIds, true)) $typeNames[] = $t['name'];

    require __DIR__ . '/../../views/companies/view.php';
  }

  public static function save() {
    csrf_verify();

    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

    // Normalizza input
    $data = self::sanitizeCompanyPost($_POST);
    $typeIds = array_map('intval', $_POST['type_ids'] ?? []);

    // Validazioni
    $errors = self::validate($data, $typeIds);
    if ($errors) {
      foreach ($errors as $e) flash('error', $e);
      // ricarica form mantenendo id
      $redir = $id ? "/companies/new?id=$id" : "/companies/new";
      redirect($redir);
    }

    try {
      $companyId = CompanyRepository::save($data, $typeIds, $id);
      flash('success', $id ? 'Azienda aggiornata' : 'Azienda creata');
      redirect("/companies/view?id=$companyId");
    } catch (Throwable $e) {
      flash('error', "Errore salvataggio: ".$e->getMessage());
      $redir = $id ? "/companies/new?id=$id" : "/companies/new";
      redirect($redir);
    }
  }

  public static function delete() {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    try {
      CompanyRepository::delete($id);
      flash('success', 'Azienda eliminata');
    } catch (Throwable $e) {
      flash('error', 'Errore eliminazione: '.$e->getMessage());
    }
    redirect('/companies');
  }

  // ----------------- Helpers -----------------

  private static function sanitizeCompanyPost(array $p): array {
    $trim = fn($k) => isset($p[$k]) ? trim((string)$p[$k]) : null;
    $nul = fn($v) => ($v === null || $v === '') ? null : $v;

    // turnover: accetta "1.234,56" oppure "1234.56"
    $turnoverRaw = $trim('turnover_eur') ?? '';
    $turnoverNorm = str_replace(['.',' '], '', $turnoverRaw);
    $turnoverNorm = str_replace(',', '.', $turnoverNorm);

    return [
      'vat_number' => $trim('vat_number'),
      'tax_code' => $nul($trim('tax_code')),
      'business_name' => $trim('business_name'),
      'iban' => $nul($trim('iban')),

      'invoicing_code' => $nul($trim('invoicing_code')),
      'phone' => $nul($trim('phone')),
      'pec_email' => $nul($trim('pec_email')),
      'email' => $nul($trim('email')),
      'website_url' => $nul($trim('website_url')),

      'street' => $nul($trim('street')),
      'street_no' => $nul($trim('street_no')),
      'cap' => $nul($trim('cap')),
      'city' => $nul($trim('city')),
      'province' => $nul($trim('province')),

      'rco_user_id' => !empty($p['rco_user_id']) ? (int)$p['rco_user_id'] : null,
      'referred_by_user_id' => !empty($p['referred_by_user_id']) ? (int)$p['referred_by_user_id'] : null,

      'category' => $nul($trim('category')),
      'ea_code' => $nul($trim('ea_code')),

      'staff_avg_range' => $trim('staff_avg_range'),
      'turnover_eur' => (float)$turnoverNorm,

      'known_as' => $nul($trim('known_as')),
      'main_products' => $nul($trim('main_products')),
      'notes' => $nul($trim('notes')),

      'promoter_company_id' => !empty($p['promoter_company_id']) ? (int)$p['promoter_company_id'] : null,
    ];
  }

  private static function validate(array $d, array $typeIds): array {
    $err = [];

    // P.IVA: obbligatoria 11 cifre
    if (!$d['vat_number'] || !preg_match('/^\d{11}$/', $d['vat_number'])) {
      $err[] = "Partita IVA obbligatoria: deve contenere esattamente 11 numeri.";
    }

    // Ragione sociale: obbligatoria max 30
    if (!$d['business_name']) $err[] = "Ragione Sociale obbligatoria.";
    if ($d['business_name'] && mb_strlen($d['business_name']) > 30) {
      $err[] = "Ragione Sociale: massimo 30 caratteri.";
    }

    // Codice fiscale: se presente 11 cifre o 16 alfanumerico
    if ($d['tax_code'] !== null) {
      $tc = strtoupper($d['tax_code']);
      if (!(preg_match('/^\d{11}$/', $tc) || preg_match('/^[A-Z0-9]{16}$/', $tc))) {
        $err[] = "Codice Fiscale non valido: deve essere 11 numeri oppure 16 caratteri alfanumerici.";
      }
    }

    // Organico medio range obbligatorio (es. "1-5", "6-15"... lato app)
    if (!$d['staff_avg_range']) {
      $err[] = "Organico Medio obbligatorio (in forma di range, es. 1-5).";
    } else {
      // accetta "1-5" oppure "1 - 5"
      if (!preg_match('/^\s*\d+\s*-\s*\d+\s*$/', $d['staff_avg_range'])) {
        $err[] = "Organico Medio: usa un range nel formato 1-5, 6-15, ecc.";
      }
    }

    // Fatturato obbligatorio > 0
    if (!isset($d['turnover_eur']) || $d['turnover_eur'] <= 0) {
      $err[] = "Fatturato obbligatorio: inserisci un importo in Euro maggiore di 0.";
    }

    // Tipologie: consigliato almeno 1
    if (!$typeIds) {
      $err[] = "Tipologia di Azienda: seleziona almeno un valore (Potenziale Cliente / Promotore / Fornitore / Partner).";
    }

    // Email/PEC (se presenti) formato base
    if ($d['email'] && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err[] = "Email non valida.";
    if ($d['pec_email'] && !filter_var($d['pec_email'], FILTER_VALIDATE_EMAIL)) $err[] = "PEC non valida.";

    // IBAN (se presente) check semplice
    if ($d['iban'] !== null) {
      $iban = strtoupper(str_replace(' ', '', $d['iban']));
      if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban)) {
        $err[] = "IBAN non valido (controllo formale).";
      }
    }

    // CAP (se presente) 5 cifre (Italia) – puoi rilassarlo se vuoi
    if ($d['cap'] !== null && !preg_match('/^\d{5}$/', $d['cap'])) {
      $err[] = "CAP non valido: deve avere 5 numeri.";
    }

    return $err;
  }

  public static function categories(): array {
    return [
      'Agroalimentare','Commerciale','Commercio','Edile','Ente','Formazione','Fornitore',
      'Impiantistica','Manifatturiere','Officine','Privato','Sanità','Servizi',
      'Strategico','Trasporti','Turismo'
    ];
  }

  public static function knownAs(): array {
    return ['Fiera','Newsletter','Pagine Gialle','Promotore','Pubblicità','Segnalazione','Sito','Telemarketing'];
  }

  public static function eaCodes(): array {
    return [
      'Agricoltura e pesca',
      'Estrazione minerali (cave, miniere e giacimenti petroliferi',
      'Prodotti farmaceutici',
      'Calce, gesso, calcestruzzo, cemento e relativi prodotti',
      'Metalli e loro leghe',
      'fabbricazione dei prodotti in metallo',
      'Macchine apparecchi e impianti meccanici',
      'Macchine elettriche e apparecchiature elettriche e ottiche',
      'Produzione non altrimenti classificata',
      'Produzione e distribuzione di energia elettrica',
      'Imprese di costruzione',
      'Industrie alimentari, delle bevande e del tabacco',
      'Alberghi, ristoranti e bar',
      'Trasporti, magazzinaggi e comunicazioni',
      'Intermediazione finanziaria',
      'attività immobiliari, noleggio, software (ICT)',
      'Servizi diversi',
      'Pubblica amministrazione',
      'Istruzione',
      'Sanità e altri servizi sociali',
      'Servizi pubblici diversi',
      'Prodotti tessili (semilavorati, prodotti finiti e abbigliamento)',
      'Tipografia e attività connesse alla stampa'
    ];
  }
}
