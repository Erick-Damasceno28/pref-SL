<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Cadastro Imobiliário') ?> — Prefeitura de São Leopoldo</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<?php
$currentFile = basename($_SERVER['PHP_SELF']);
function navActive(string $file): string {
    global $currentFile;
    return $currentFile === $file ? 'nav-link active' : 'nav-link';
}
$user = currentUser();
?>

<nav class="navbar">
  <a class="navbar-brand" href="<?= BASE_URL ?>/pages/dashboard.php">
    <div class="brand-icon"><i class="bi bi-buildings-fill"></i></div>
    <div>
      <div class="brand-title">Cadastro Imobiliário</div>
      <div class="brand-sub">Prefeitura de São Leopoldo</div>
    </div>
  </a>

  <div class="navbar-links">
    <a href="<?= BASE_URL ?>/pages/dashboard.php" class="<?= navActive('dashboard.php') ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= BASE_URL ?>/pages/pessoas.php" class="<?= navActive('pessoas.php') ?>">
      <i class="bi bi-people"></i> Pessoas
    </a>
    <a href="<?= BASE_URL ?>/pages/imoveis.php" class="<?= navActive('imoveis.php') ?>">
      <i class="bi bi-house-door"></i> Imóveis
    </a>
  </div>

  <div class="navbar-right">
    <span class="user-pill">
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($user['nome']) ?>
    </span>
    <a href="<?= BASE_URL ?>/logout.php" class="btn-logout">
      <i class="bi bi-box-arrow-right"></i> Sair
    </a>
  </div>
</nav>

<main>
