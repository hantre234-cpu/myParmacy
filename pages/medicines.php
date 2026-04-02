<?php
require_once '../config/db.php';
$title = 'Gestion des Médicaments'; $page = 'medicines';
$pdo = getPDO();

// ── ACTIONS ──────────────────────────────────────────────
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $pdo->prepare("INSERT INTO medicines (name,description,category,price,stock,unit,expiry_date) VALUES (?,?,?,?,?,?,?)")
        ->execute([
            trim($_POST['name']), trim($_POST['description']),
            trim($_POST['category']), (float)$_POST['price'],
            (int)$_POST['stock'], trim($_POST['unit']),
            $_POST['expiry_date'] ?: null
        ]);
    setFlash('success', "Médicament ajouté avec succès.");
    redirect('medicines.php');
}

if ($action === 'edit') {
    $pdo->prepare("UPDATE medicines SET name=?,description=?,category=?,price=?,stock=?,unit=?,expiry_date=? WHERE id=?")
        ->execute([
            trim($_POST['name']), trim($_POST['description']),
            trim($_POST['category']), (float)$_POST['price'],
            (int)$_POST['stock'], trim($_POST['unit']),
            $_POST['expiry_date'] ?: null, (int)$_POST['id']
        ]);
    setFlash('success', "Médicament mis à jour.");
    redirect('medicines.php');
}

if ($action === 'delete') {
    $pdo->prepare("DELETE FROM medicines WHERE id=?")->execute([(int)$_POST['id']]);
    setFlash('success', "Médicament supprimé.");
    redirect('medicines.php');
}

// ── FETCH ────────────────────────────────────────────────
$search   = trim($_GET['q'] ?? '');
$category = trim($_GET['cat'] ?? '');
$where    = []; $params = [];

if ($search)   { $where[] = "(name LIKE ? OR description LIKE ?)"; $like="%$search%"; $params=array_merge($params,[$like,$like]); }
if ($category) { $where[] = "category=?"; $params[] = $category; }

$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
$meds = $pdo->prepare("SELECT * FROM medicines $whereSQL ORDER BY name ASC");
$meds->execute($params);
$meds = $meds->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM medicines WHERE category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <div class="card-title">💊 Liste des Médicaments <span class="badge badge-blue"><?= count($meds) ?></span></div>
    <button class="btn btn-primary" data-modal="modal-add">+ Ajouter un médicament</button>
  </div>

  <!-- Search & Filter -->
  <form method="GET" class="search-bar">
    <div class="search-input-wrap">
      <span class="si">🔍</span>
      <input type="text" name="q" placeholder="Rechercher un médicament…" value="<?= e($search) ?>">
    </div>
    <select name="cat" style="padding:9px 12px;border:1px solid var(--slate-300);border-radius:var(--radius-sm);font-family:var(--font);font-size:.88rem;">
      <option value="">Toutes catégories</option>
      <?php foreach($categories as $cat): ?>
      <option value="<?=e($cat)?>" <?= $category===$cat?'selected':'' ?>><?= e($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary">Filtrer</button>
    <?php if($search||$category): ?><a href="medicines.php" class="btn btn-secondary">✕ Effacer</a><?php endif; ?>
  </form>

  <?php if($meds): ?>
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Unité</th><th>Expiration</th><th>Statut</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach($meds as $m): ?>
        <?php
          $expiry  = $m['expiry_date'] ? new DateTime($m['expiry_date']) : null;
          $today   = new DateTime();
          $expired = $expiry && $expiry < $today;
          $soon    = $expiry && !$expired && $expiry->diff($today)->days <= 90;
        ?>
        <tr>
          <td><?= e($m['id']) ?></td>
          <td><strong><?= e($m['name']) ?></strong><br><small style="color:var(--slate-500)"><?= e(substr($m['description'],0,50)) ?></small></td>
          <td><span class="badge badge-blue"><?= e($m['category'] ?: '—') ?></span></td>
          <td><?= number_format($m['price'],2) ?> DA</td>
          <td>
            <?= e($m['stock']) ?>
            <?php if($m['stock']==0): ?><span class="badge badge-red">Rupture</span>
            <?php elseif($m['stock']<10): ?><span class="badge badge-red">Critique</span>
            <?php elseif($m['stock']<20): ?><span class="badge badge-amber">Faible</span>
            <?php endif; ?>
          </td>
          <td><?= e($m['unit']) ?></td>
          <td><?= $expiry ? $expiry->format('d/m/Y') : '—' ?></td>
          <td>
            <?php if($expired): ?><span class="badge badge-red">Expiré</span>
            <?php elseif($soon): ?><span class="badge badge-amber">Bientôt</span>
            <?php else: ?><span class="badge badge-green">Valide</span>
            <?php endif; ?>
          </td>
          <td class="td-actions">
            <button class="btn btn-warning btn-sm"
              onclick="openEdit(<?= htmlspecialchars(json_encode($m),ENT_QUOTES) ?>)">✏️</button>
            <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce médicament ?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id"     value="<?= e($m['id']) ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="empty-state"><div class="empty-icon">💊</div><p>Aucun médicament trouvé.</p></div>
  <?php endif; ?>
</div>

<!-- Modal Add -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header"><h3>➕ Ajouter un médicament</h3><button class="modal-close">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full"><label>Nom *</label><input type="text" name="name" required placeholder="ex: Doliprane 1000mg"></div>
          <div class="form-group"><label>Catégorie</label><input type="text" name="category" placeholder="ex: Analgésique"></div>
          <div class="form-group"><label>Unité</label>
            <select name="unit">
              <option value="boîte">Boîte</option><option value="flacon">Flacon</option>
              <option value="comprimé">Comprimé</option><option value="ampoule">Ampoule</option><option value="pcs">Pièce</option>
            </select>
          </div>
          <div class="form-group"><label>Prix (DA) *</label><input type="number" name="price" step="0.01" min="0" required placeholder="0.00"></div>
          <div class="form-group"><label>Stock *</label><input type="number" name="stock" min="0" required placeholder="0"></div>
          <div class="form-group"><label>Date d'expiration</label><input type="date" name="expiry_date"></div>
          <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Description du médicament…"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal">
    <div class="modal-header"><h3>✏️ Modifier le médicament</h3><button class="modal-close">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full"><label>Nom *</label><input type="text" name="name" id="edit-name" required></div>
          <div class="form-group"><label>Catégorie</label><input type="text" name="category" id="edit-category"></div>
          <div class="form-group"><label>Unité</label>
            <select name="unit" id="edit-unit">
              <option value="boîte">Boîte</option><option value="flacon">Flacon</option>
              <option value="comprimé">Comprimé</option><option value="ampoule">Ampoule</option><option value="pcs">Pièce</option>
            </select>
          </div>
          <div class="form-group"><label>Prix (DA) *</label><input type="number" name="price" id="edit-price" step="0.01" min="0" required></div>
          <div class="form-group"><label>Stock *</label><input type="number" name="stock" id="edit-stock" min="0" required></div>
          <div class="form-group"><label>Date d'expiration</label><input type="date" name="expiry_date" id="edit-expiry_date"></div>
          <div class="form-group full"><label>Description</label><textarea name="description" id="edit-description"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(d) {
  ['id','name','category','price','stock','unit','description'].forEach(k => {
    const el = document.getElementById('edit-' + k);
    if (el) el.value = d[k] ?? '';
  });
  const exp = document.getElementById('edit-expiry_date');
  if (exp) exp.value = d.expiry_date ? d.expiry_date.substring(0,10) : '';
  document.getElementById('modal-edit').classList.add('open');
}
</script>

<?php include '../includes/footer.php'; ?>
