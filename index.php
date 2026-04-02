<?php
require_once 'config/db.php';
$title = 'Tableau de bord'; $page = 'home';
$pdo = getPDO();
$stats = [
  'sellers'   => $pdo->query('SELECT COUNT(*) FROM sellers')->fetchColumn(),
  'medicines' => $pdo->query('SELECT COUNT(*) FROM medicines')->fetchColumn(),
  'caisses'   => $pdo->query("SELECT COUNT(*) FROM cash_registers WHERE status='open'")->fetchColumn(),
  'today'     => $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM sales WHERE DATE(sale_date)=CURDATE()")->fetchColumn(),
];
$recentSales = $pdo->query("
  SELECT s.quantity, s.total_price, s.sale_date, m.name AS med,
         CONCAT(sel.first_name,' ',sel.last_name) AS seller
  FROM sales s
  JOIN medicines m ON m.id=s.medicine_id
  JOIN cash_registers cr ON cr.id=s.cash_register_id
  JOIN sellers sel ON sel.id=cr.seller_id
  ORDER BY s.sale_date DESC LIMIT 5
")->fetchAll();
$lowStock = $pdo->query("SELECT name, stock FROM medicines WHERE stock < 20 ORDER BY stock ASC LIMIT 5")->fetchAll();
include 'includes/header.php';
?>
<div class="hero">
  <div class="hero-text">
    <h2>Bienvenue sur PharmaCare 👋</h2>
    <p>Gérez vos vendeurs, médicaments, caisses et ventes depuis une interface centralisée et intuitive.</p>
  </div>
  <div class="hero-emoji">🏥</div>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon">👤</div>
    <div class="stat-info"><div class="stat-value"><?=e($stats['sellers'])?></div><div class="stat-label">Vendeurs</div></div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon">💊</div>
    <div class="stat-info"><div class="stat-value"><?=e($stats['medicines'])?></div><div class="stat-label">Médicaments</div></div>
  </div>
  <div class="stat-card amber">
    <div class="stat-icon">🗃️</div>
    <div class="stat-info"><div class="stat-value"><?=e($stats['caisses'])?></div><div class="stat-label">Caisses ouvertes</div></div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon">💰</div>
    <div class="stat-info"><div class="stat-value"><?=number_format($stats['today'],0,'.',' ')?> DA</div><div class="stat-label">Ventes aujourd'hui</div></div>
  </div>
</div>

<h2 style="font-size:.82rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">Accès rapide</h2>
<div class="quick-links">
  <a href="pages/sellers.php"        class="quick-link"><div class="ql-icon">👤</div><div class="ql-label">Vendeurs</div></a>
  <a href="pages/medicines.php"      class="quick-link"><div class="ql-icon">💊</div><div class="ql-label">Médicaments</div></a>
  <a href="pages/cash_registers.php" class="quick-link"><div class="ql-icon">🗃️</div><div class="ql-label">Caisses</div></a>
  <a href="pages/sales.php"          class="quick-link"><div class="ql-icon">🧾</div><div class="ql-label">Ventes</div></a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:24px">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🧾 Ventes récentes</div>
      <a href="pages/sales.php" class="btn btn-secondary btn-sm">Voir tout</a>
    </div>
    <?php if($recentSales): ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Médicament</th><th>Qté</th><th>Total</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach($recentSales as $r): ?>
        <tr><td><?=e($r['med'])?></td><td><?=e($r['quantity'])?></td><td><?=number_format($r['total_price'],2)?> DA</td><td><?=date('d/m H:i',strtotime($r['sale_date']))?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?><div class="empty-state"><div class="empty-icon">🧾</div><p>Aucune vente enregistrée</p></div><?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">⚠️ Stock faible (&lt;20)</div>
      <a href="pages/medicines.php" class="btn btn-secondary btn-sm">Gérer</a>
    </div>
    <?php if($lowStock): ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Médicament</th><th>Stock</th><th>Statut</th></tr></thead>
      <tbody>
        <?php foreach($lowStock as $m): ?>
        <tr><td><?=e($m['name'])?></td><td><?=e($m['stock'])?></td>
        <td><span class="badge <?= $m['stock']==0?'badge-red':($m['stock']<10?'badge-red':'badge-amber') ?>"><?= $m['stock']==0?'Rupture':($m['stock']<10?'Critique':'Faible') ?></span></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?><div class="empty-state"><div class="empty-icon">✅</div><p>Stocks suffisants</p></div><?php endif; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
