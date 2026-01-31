<?php $cfg = require __DIR__ . '/../../app/config/config.php'; $u = current_user(); $flash = flash_get(); ?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($cfg['app']['name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(($cfg['app']['base_url'] ?: '').'/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
  <button class="btn btn-outline-light me-2 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
    ☰
  </button>
  <a class="navbar-brand fw-semibold" href="<?= e(($cfg['app']['base_url'] ?: '').'/') ?>">
    <span class="me-2">🧩</span><?= e($cfg['app']['name']) ?>
  </a>

  <div class="ms-auto d-flex align-items-center gap-2">
    <?php if ($u): ?>
      <span class="text-white-50 small d-none d-md-inline"><?= e($u['name']) ?></span>
      <form method="POST" action="<?= e(($cfg['app']['base_url'] ?: '').'/logout') ?>" class="m-0">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-light">Logout</button>
      </form>
    <?php endif; ?>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
