<?php
function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $config = require __DIR__ . '/../config/config.php';
  $pdo = new PDO(
    $config['db']['dsn'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['opts']
  );
  return $pdo;
}