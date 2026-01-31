<?php
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function redirect(string $path): void {
  $base = (require __DIR__ . '/../config/config.php')['app']['base_url'];
  header("Location: " . ($base ?: '') . $path);
  exit;
}

function flash(string $type, string $msg): void { $_SESSION['flash'][$type][] = $msg; }
function flash_get(): array {
  $f = $_SESSION['flash'] ?? [];
  unset($_SESSION['flash']);
  return $f;
}
