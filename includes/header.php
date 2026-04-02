<?php
// includes/header.php
if(!isset($page))  $page  = 'home';
if(!isset($title)) $title = 'PharmaCare';
$inPages = strpos($_SERVER['PHP_SELF'],'/pages/') !== false;
$base    = $inPages ? '../' : '';
$pBase   = $inPages ? ''    : 'pages/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PharmaCare – Système de gestion de pharmacie: médicaments, vendeurs, caisse et ventes.">
  <meta name="keywords"    content="pharmacie, gestion pharmacie, médicaments, ventes, caisse, PharmaCare, UHBC">
  <meta name="author"      content="PharmaCare System">
  <title><?= e($title) ?> | PharmaCare</title>
  <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">💊</div>
      <div class="logo-text">
        <strong>PharmaCare</strong>
        <span>Gestion Pharmacie · UHBC</span>
      </div>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-group-label">Principal</p>
      <ul>
        <li class="nav-item">
          <a href="<?= $base ?>index.php" class="<?= $page==='home'?'active':'' ?>">
            <span class="icon">🏠</span> Tableau de bord
          </a>
        </li>
      </ul>
      <p class="nav-group-label">Gestion</p>
      <ul>
        <li class="nav-item">
          <a href="<?= $pBase ?>sellers.php" class="<?= $page==='sellers'?'active':'' ?>">
            <span class="icon">👤</span> Vendeurs
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= $pBase ?>medicines.php" class="<?= $page==='medicines'?'active':'' ?>">
            <span class="icon">💉</span> Médicaments
          </a>
        </li>
      </ul>
      <p class="nav-group-label">Caisse &amp; Ventes</p>
      <ul>
        <li class="nav-item">
          <a href="<?= $pBase ?>cash_registers.php" class="<?= $page==='cash_registers'?'active':'' ?>">
            <span class="icon">🗃️</span> Caisses
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= $pBase ?>sales.php" class="<?= $page==='sales'?'active':'' ?>">
            <span class="icon">🧾</span> Ventes
          </a>
        </li>
      </ul>
    </nav>
    <div class="sidebar-footer">&copy; <?= date('Y') ?> PharmaCare · UHBC FSEI</div>
  </aside>

  <div class="main-area">
    <header class="topbar">
      <div class="topbar-title">
        <h1><?= e($title) ?></h1>
        <p><?= date('l, d F Y') ?></p>
      </div>
      <span class="topbar-badge">💊 PharmaCare</span>
    </header>
    <main class="page-content">
<?php
$flash = getFlash();
if ($flash):
  $icons = ['success'=>'✅','error'=>'❌','warning'=>'⚠️','info'=>'ℹ️'];
?>
<div class="alert alert-<?= e($flash['type']) ?>">
  <?= $icons[$flash['type']] ?? 'ℹ️' ?> <?= e($flash['msg']) ?>
</div>
<?php endif; ?>
