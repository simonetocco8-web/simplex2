<?php
class ProtocolService {

  public static function next(string $keyPrefix, int $year, PDO $pdo): int {
    $key = $keyPrefix . '_' . $year; // es: offer_2026, job_2026, invoice_2026

    // LOCK riga contatore (pattern robusto)
    $stmt = $pdo->prepare("SELECT counter_value FROM counters WHERE counter_key=? FOR UPDATE");
    $stmt->execute([$key]);
    $row = $stmt->fetch();

    if (!$row) {
      // prima volta: inserisco a 1
      $pdo->prepare("INSERT INTO counters(counter_key,counter_value) VALUES(?,1)")->execute([$key]);
      return 1;
    }

    $next = ((int)$row['counter_value']) + 1;
    $pdo->prepare("UPDATE counters SET counter_value=? WHERE counter_key=?")->execute([$next, $key]);
    return $next;
  }

  public static function formatOffer(int $no, int $year): string {
    return "Off/$no/$year";
  }
  public static function formatJob(int $no, int $year): string {
    return "C/$no/$year";
  }
  public static function formatJobCode(int $year, int $seq): string {
    return sprintf("ibis-%d-%06d", $year, $seq);
  }
}
