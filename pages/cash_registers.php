<?php
require_once '../config/db.php';
$title = 'Gestion des Caisses'; $page = 'cash_registers';
$pdo = getPDO();

$action = $_POST['action'] ?? '';

// ── Add new register ───────────────────────────────────────
if ($action === 'add') {
    $pdo->prepare("INSERT INTO cash_registers (seller_id,label,opening_time,opening_amount,status) VALUES (?,?,?,?,'open')")
        ->execute([
            (int)$_POST['seller_id'],
            trim($_POST['label']),
            date('Y-m-d H:i:s'),
            (float)$_POST['opening_amount']
        ]);
    setFlash('success', "Caisse créée et ouverte avec succès.");
    redirect('cash_registers.php');
}

// ── Close register ────────────────────────────────────────
if ($action === 'close') {
    $pdo->prepare("UPDATE cash_registers SET status='closed', closing_time=NOW(), closing_amount=? WHERE id=?")
        ->execute([(float)$_POST['closing_amount'], (int)$_POST['id']]);
    setFlash('success', "Caisse fermée.");
    redirect('cash_registers.php');
}

// ── Reopen ───────────────────────────────────────────────
if ($action === 'reopen') {
    $pdo->prepare("UPDATE cash_registers SET status='open', closing_time=NULL, closing_amount=NULL WHERE id=?")
        ->execute([(int)$_POST['id']]);
    setFlash('success', "Caisse réouverte.");
    redirect('cash_registers.php');
}

// ── Delete ────────────────────────────────────────────────
if ($action === 'delete') {
    $pdo->prepare("DELETE FROM cash_registers WHERE id=?")->execute([(int)$_POST['id']]);
    setFlash('success', "Caisse supprimée.");
    redirect('cash_registers.php');
}

// ── Fetch ─────────────────────────────────────────────────
$registers = $pdo->query("
    SELECT cr.*, CONCAT(s.first_name,' ',s.last_name) AS seller_name,
           (SELECT COALESCE(SUM(total_price),0) FROM sales WHERE cash_register_id=cr.id) AS total_sales,
           (SELECT COUNT(*) FROM sales WHERE cash_register_id=cr.id) AS nb_sales
    FROM cash_registers cr
    JOIN sellers s ON s.id=cr.seller_id
    ORDER BY cr.created_at DESC
")->fetchAll();

$sellers = $pdo->query("SELECT id, CONCAT(first_name,' ',last_name) AS name FROM sellers ORDER BY first_name")->fetchAll();

// Caisse to close (for modal)
$toClose = null;
if (isset($_GET['close'])) {
    $toClose = $pdo->prepare("SELECT cr.*, CONCAT(s.first_name,' ',s.last_name) AS seller_name,
        (SELECT COALESCE(SUM(total_price),0) FROM sales WHERE cash_register_id=cr.id) AS total_sales
        FROM cash_registers cr JOIN sellers s ON s.id=cr.seller_id WHERE cr.id=?");
    $toClose->execute([(int)$_GET['close']]);
    $toClose = $toClose->fetch();
}

include '../includes/header.php';
?>

<!-- Stats mini -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:24px">
  <?php
    $open   = array_filter($registers, fn($r)=>$r['status']==='open');
    $closed = array_filter($registers, fn($r)=>$r['status']==='closed');
    $totalRevenu = array_sum(array_column($registers,'total_sales'));
  ?>
  <div class="stat-card"><div class="stat-icon">🟢</div><div class="stat-info"><div class="stat-value"><?=count($open)?></div><div class="stat-label">Caisses ouvertes</div></div></div>
  <div class="stat-card amber"><div class="stat-icon">🔴</div><div class="stat-info"><div class="stat-value"><?=count($closed)?></div><div class="stat-label">Caisses fermées</div></div></div>
  <div class="stat-card blue"><div class="stat-icon">💰</div><div class="stat-info"><div class="stat-value"><?=number_format($totalRevenu,0,'.',' ')?> DA</div><div class="stat-label">Revenu total</div></div></div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">🗃️ Caisses enregistreuses <span class="badge badge-blue"><?= count($registers) ?></span></div>
    <button class="btn btn-primary" data-modal="modal-add">+ Ouvrir une caisse</button>
  </div>

  <?php if ($registers): ?>
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Libellé</th><th>Vendeur</th><th>Ouverture</th><th>Fermeture</th>
        <th>Mt. Ouv.</th><th>Mt. Ferm.</th><th>Ventes</th><th>Nb Ventes</th><th>Statut</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach($registers as $r): ?>
        <tr>
          <td><?= e($r['id']) ?></td>
          <td><strong><?= e($r['label']) ?></strong></td>
          <td><?= e($r['seller_name']) ?></td>
          <td><?= $r['opening_time'] ? date('d/m/Y H:i', strtotime($r['opening_time'])) : '—' ?></td>
          <td><?= $r['closing_time'] ? date('d/m/Y H:i', strtotime($r['closing_time'])) : '—' ?></td>
          <td><?= number_format($r['opening_amount'],2) ?> DA</td>
          <td><?= $r['closing_amount'] !== null ? number_format($r['closing_amount'],2).' DA' : '—' ?></td>
          <td><strong><?= number_format($r['total_sales'],2) ?> DA</strong></td>
          <td><?= e($r['nb_sales']) ?></td>
          <td><span class="badge <?= $r['status']==='open'?'badge-green':'badge-slate' ?>"><?= $r['status']==='open'?'Ouverte':'Fermée' ?></span></td>
          <td class="td-actions">
            <?php if($r['status']==='open'): ?>
            <a href="?close=<?= e($r['id']) ?>" class="btn btn-warning btn-sm">🔒 Fermer</a>
            <?php else: ?>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="reopen">
              <input type="hidden" name="id" value="<?= e($r['id']) ?>">
              <button type="submit" class="btn btn-info btn-sm">🔓 Réouvrir</button>
            </form>
            <?php endif; ?>
            <a href="sales.php?cr=<?= e($r['id']) ?>" class="btn btn-secondary btn-sm">🧾 Ventes</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette caisse et toutes ses ventes ?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= e($r['id']) ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="empty-state"><div class="empty-icon">🗃️</div><p>Aucune caisse enregistrée.</p></div>
  <?php endif; ?>
</div>

<!-- Modal Add -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header"><h3>🗃️ Ouvrir une nouvelle caisse</h3><button class="modal-close">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full"><label>Libellé *</label><input type="text" name="label" required value="Caisse Principale" placeholder="ex: Caisse 1"></div>
          <div class="form-group full"><label>Vendeur responsable *</label>
            <select name="seller_id" required>
              <option value="">-- Choisir un vendeur --</option>
              <?php foreach($sellers as $s): ?>
              <option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full"><label>Montant à l'ouverture (DA) *</label><input type="number" name="opening_amount" step="0.01" min="0" required placeholder="0.00"></div>
        </div>
        <p style="font-size:.82rem;color:var(--slate-500);margin-top:12px">⏱️ L'heure d'ouverture sera enregistrée automatiquement.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">Ouvrir la caisse</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Close -->
<?php if ($toClose): ?>
<div class="modal-overlay open" id="modal-close-cr">
  <div class="modal">
    <div class="modal-header"><h3>🔒 Fermer la caisse : <?= e($toClose['label']) ?></h3><button class="modal-close" onclick="window.location='cash_registers.php'">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="close">
      <input type="hidden" name="id" value="<?= e($toClose['id']) ?>">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group"><label>Vendeur</label><input type="text" value="<?= e($toClose['seller_name']) ?>" disabled></div>
          <div class="form-group"><label>Mt. à l'ouverture</label><input type="text" value="<?= number_format($toClose['opening_amount'],2) ?> DA" disabled></div>
          <div class="form-group"><label>Total ventes réalisées</label><input type="text" value="<?= number_format($toClose['total_sales'],2) ?> DA" disabled></div>
          <div class="form-group full"><label>Montant à la fermeture (DA) *</label><input type="number" name="closing_amount" step="0.01" min="0" required placeholder="0.00"></div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="cash_registers.php" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-danger">Confirmer la fermeture</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
