<?php
return [
  'db' => [
    'dsn'  => 'mysql:host=localhost;dbname=ibis;charset=utf8mb4',
    'user' => 'ibis_user',
    'pass' => 'YOUR_PASSWORD',
    'opts' => [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ],
  ],
  'app' => [
    'name' => 'IBIS Management',
    'base_url' => '', // es: '/ibis/public' se necessario
  ],
];