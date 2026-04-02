<?php
require_once '../config/db.php';
$title = 'Gestion des Ventes'; $page = 'sales';
$pdo = getPDO();

$action = $_POST['action'] ?? '';

// ── Add Sale ──────────────────────────────────────────────
if ($action === 'add') {
    $medId = (int)$_POST['medicine_id'];
    $qty   = (int)$_POST['quantity'];
    $crId  = (int)$_POST['cash_register_id'];

    // Get medicine price and current stock
    $med = $pdo->prepare("SELECT price, stock FROM medicines WHERE id=?");
    $med->execute([$medId]);
    $med = $med->fetch();

    if (!$med) { setFlash('error','Médicament introuvable.'); redirect('sales.php'); }
    if ($med['stock'] < $qty) { setFlash('error','Stock insuffisant ('.$med['stock'].' disponible).'); redirect('sales.php'); }

    // Verify register is open
    $cr = $pdo->prepare("SELECT id FROM cash_registers WHERE id=? AND status='open'");
    $cr->execute([$crId]);
    if (!$cr->fetch()) { setFlash('error','Caisse fermée ou introuvable.'); redirect('sales.php'); }

    // Insert sale
    $pdo->prepare("INSERT INTO sales (cash_register_id,medicine_id,quantity,unit_price) VALUES (?,?,?,?)")
        ->execute([$crId, $medId, $qty, $med['price']]);

    // Decrease stock
    $pdo->prepare("UPDATE medicines SET stock = stock - ? WHERE id=?")->execute([$qty, $medId]);

    setFlash('success', "Vente enregistrée. Total: ".number_format($qty*$med['price'],2)." DA");
    redirect('sales.php'.($crId?"?cr=$crId":''));
}

// ── Delete Sale ───────────────────────────────────────────
if ($action === 'delete') {
    // Restore stock
    $sale = $pdo->prepare("SELECT medicine_id, quantity FROM sales WHERE id=?");
    $sale->execute([(int)$_POST['id']]);
    $sale = $sale->fetch();
    if ($sale) {
        $pdo->prepare("UPDATE medicines SET stock=stock+? WHERE id=?")->execute([$sale['quantity'], $sale['medicine_id']]);
        $pdo->prepare("DELETE FROM sales WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success','Vente annulée, stock restauré.');
    }
    redirect('sales.php');
}

// ── Fetch ─────────────────────────────────────────────────
$crFilter = (int)($_GET['cr'] ?? 0);
$where     = $crFilter ? 'WHERE s.cash_register_id=?' : '';
$params    = $crFilter ? [$crFilter] : [];

$sales = $pdo->prepare("
    SELECT s.id, s.quantity, s.unit_price, s.total_price, s.sale_date,
           m.name AS medicine, cr.label AS register_label,
           CONCAT(sel.first_name,' ',sel.last_name) AS seller
    FROM sales s
    JOIN medicines m      ON m.id  = s.medicine_id
    JOIN cash_registers cr ON cr.id = s.cash_register_id
    JOIN sellers sel       ON sel.id= cr.seller_id
    $where
    ORDER BY s.sale_date DESC
    LIMIT 200
");
$sales->execute($params);
$sales = $sales->fetchAll();

$totalCA = array_sum(array_column($sales,'total_price'));

// For selects in modal
$medicines = $pdo->query("SELECT id, name, price, stock FROM medicines WHERE stock>0 ORDER BY name")->fetchAll();
$registers = $pdo->query("SELECT cr.id, cr.label, CONCAT(s.first_name,' ',s.last_name) AS seller
    FROM cash_registers cr JOIN sellers s ON s.id=cr.seller_id WHERE cr.status='open' ORDER BY cr.label")->fetchAll();

include '../includes/header.php';
?>

<?php if ($crFilter): ?>
<div class="alert alert-info">
  🔍 Filtrage par caisse #<?= e($crFilter) ?>. <a href="sales.php">Voir toutes les ventes</a>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon">🧾</div><div class="stat-info"><div class="stat-value"><?= count($sales) ?></div><div class="stat-label">Ventes affichées</div></div></div>
  <div class="stat-card blue"><div class="stat-icon">💰</div><div class="stat-info"><div class="stat-value"><?= number_format($totalCA,0,'.',' ') ?> DA</div><div class="stat-label">Chiffre d'affaires</div></div></div>
  <div class="stat-card amber"><div class="stat-icon">📦</div><div class="stat-info"><div class="stat-value"><?= array_sum(array_column($sales,'quantity')) ?></div><div class="stat-label">Unités vendues</div></div></div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">🧾 Ventes enregistrées</div>
    <?php if(count($registers)>0): ?>
    <button class="btn btn-primary" data-modal="modal-add">+ Nouvelle vente</button>
    <?php else: ?>
    <span class="badge badge-amber">⚠️ Aucune caisse ouverte</span>
    <?php endif; ?>
  </div>

  <!-- Filter by register -->
  <form method="GET" class="search-bar">
    <select name="cr" style="padding:9px 12px;border:1px solid var(--slate-300);border-radius:var(--radius-sm);font-family:var(--font);font-size:.88rem;">
      <option value="">Toutes les caisses</option>
      <?php
        $allCR = $pdo->query("SELECT cr.id, cr.label FROM cash_registers cr ORDER BY cr.label")->fetchAll();
        foreach($allCR as $cr): ?>
        <option value="<?= e($cr['id']) ?>" <?= $crFilter===$cr['id']?'selected':'' ?>><?= e($cr['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary">Filtrer</button>
    <?php if($crFilter): ?><a href="sales.php" class="btn btn-secondary">✕ Effacer</a><?php endif; ?>
  </form>

  <?php if($sales): ?>
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Médicament</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th><th>Caisse</th><th>Vendeur</th><th>Date</th><th>Action</th>
      </tr></thead>
      <tbody>
        <?php foreach($sales as $s): ?>
        <tr>
          <td><?= e($s['id']) ?></td>
          <td><?= e($s['medicine']) ?></td>
          <td><?= e($s['quantity']) ?></td>
          <td><?= number_format($s['unit_price'],2) ?> DA</td>
          <td><strong><?= number_format($s['total_price'],2) ?> DA</strong></td>
          <td><?= e($s['register_label']) ?></td>
          <td><?= e($s['seller']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($s['sale_date'])) ?></td>
          <td>
            <form method="POST" style="display:inline" onsubmit="return confirm('Annuler cette vente ? Le stock sera restauré.')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= e($s['id']) ?>">
              <button type="submit" class="btn btn-danger btn-sm">↩️ Annuler</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="background:var(--green-50)">
          <td colspan="4" style="text-align:right;font-weight:600;padding:12px 14px">Total :</td>
          <td style="font-weight:700;padding:12px 14px"><?= number_format($totalCA,2) ?> DA</td>
          <td colspan="4"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <?php else: ?>
  <div class="empty-state"><div class="empty-icon">🧾</div><p>Aucune vente enregistrée.</p></div>
  <?php endif; ?>
</div>

<!-- Modal Add Sale -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header"><h3>🧾 Enregistrer une vente</h3><button class="modal-close">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full">
            <label>Caisse (ouverte) *</label>
            <select name="cash_register_id" required>
              <option value="">-- Choisir une caisse --</option>
              <?php foreach($registers as $r): ?>
              <option value="<?= e($r['id']) ?>" <?= $crFilter===$r['id']?'selected':'' ?>><?= e($r['label']) ?> — <?= e($r['seller']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full">
            <label>Médicament *</label>
            <select name="medicine_id" id="med-select" required onchange="updatePrice(this)">
              <option value="">-- Choisir un médicament --</option>
              <?php foreach($medicines as $m): ?>
              <option value="<?= e($m['id']) ?>" data-price="<?= e($m['price']) ?>" data-stock="<?= e($m['stock']) ?>">
                <?= e($m['name']) ?> — <?= number_format($m['price'],2) ?> DA (stock: <?= e($m['stock']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Quantité *</label>
            <input type="number" name="quantity" id="qty-input" min="1" value="1" required onchange="calcTotal()">
          </div>
          <div class="form-group">
            <label>Prix unitaire</label>
            <input type="text" id="price-display" disabled placeholder="—" style="background:var(--slate-100)">
          </div>
          <div class="form-group full">
            <label>Total estimé</label>
            <input type="text" id="total-display" disabled placeholder="—" style="background:var(--green-100);font-weight:700;color:var(--green-800)">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer la vente</button>
      </div>
    </form>
  </div>
</div>

<script>
function updatePrice(sel) {
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('price-display').value = opt.dataset.price ? opt.dataset.price + ' DA' : '—';
  calcTotal();
}
function calcTotal() {
  const sel = document.getElementById('med-select');
  const opt = sel.options[sel.selectedIndex];
  const price = parseFloat(opt.dataset.price) || 0;
  const qty   = parseInt(document.getElementById('qty-input').value) || 0;
  document.getElementById('total-display').value = price && qty ? (price * qty).toFixed(2) + ' DA' : '—';
}
</script>

<?php include '../includes/footer.php'; ?>
