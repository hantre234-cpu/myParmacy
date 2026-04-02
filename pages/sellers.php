<?php
require_once '../config/db.php';
$title = 'Gestion des Vendeurs'; $page = 'sellers';
$pdo = getPDO();

// ── ACTIONS ──────────────────────────────────────────────
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO sellers (first_name,last_name,email,phone,address) VALUES (?,?,?,?,?)");
    try {
        $stmt->execute([
            trim($_POST['first_name']), trim($_POST['last_name']),
            trim($_POST['email']),      trim($_POST['phone']),
            trim($_POST['address'])
        ]);
        setFlash('success', "Vendeur ajouté avec succès.");
    } catch (PDOException $e) {
        setFlash('error', "Email déjà utilisé ou erreur: " . $e->getMessage());
    }
    redirect('sellers.php');
}

if ($action === 'edit') {
    $stmt = $pdo->prepare("UPDATE sellers SET first_name=?,last_name=?,email=?,phone=?,address=? WHERE id=?");
    try {
        $stmt->execute([
            trim($_POST['first_name']), trim($_POST['last_name']),
            trim($_POST['email']),      trim($_POST['phone']),
            trim($_POST['address']),    (int)$_POST['id']
        ]);
        setFlash('success', "Vendeur mis à jour.");
    } catch (PDOException $e) {
        setFlash('error', "Erreur: " . $e->getMessage());
    }
    redirect('sellers.php');
}

if ($action === 'delete') {
    $pdo->prepare("DELETE FROM sellers WHERE id=?")->execute([(int)$_POST['id']]);
    setFlash('success', "Vendeur supprimé.");
    redirect('sellers.php');
}

// ── FETCH ────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$where  = '';
$params = [];
if ($search) {
    $where  = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $like   = "%$search%";
    $params = [$like,$like,$like,$like];
}
$sellers = $pdo->prepare("SELECT * FROM sellers $where ORDER BY created_at DESC");
$sellers->execute($params);
$sellers = $sellers->fetchAll();

include '../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <div class="card-title">👤 Liste des Vendeurs <span class="badge badge-blue"><?= count($sellers) ?></span></div>
    <button class="btn btn-primary" data-modal="modal-add">+ Ajouter un vendeur</button>
  </div>

  <!-- Search -->
  <form method="GET" class="search-bar">
    <div class="search-input-wrap">
      <span class="si">🔍</span>
      <input type="text" name="q" placeholder="Rechercher par nom, email, téléphone…" value="<?= e($search) ?>">
    </div>
    <button type="submit" class="btn btn-secondary">Rechercher</button>
    <?php if($search): ?><a href="sellers.php" class="btn btn-secondary">✕ Effacer</a><?php endif; ?>
  </form>

  <!-- Table -->
  <?php if($sellers): ?>
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Adresse</th><th>Inscrit le</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach($sellers as $s): ?>
        <tr>
          <td><?= e($s['id']) ?></td>
          <td><?= e($s['first_name']) ?></td>
          <td><strong><?= e($s['last_name']) ?></strong></td>
          <td><?= e($s['email']) ?></td>
          <td><?= e($s['phone']) ?></td>
          <td><?= e($s['address'] ?: '—') ?></td>
          <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
          <td class="td-actions">
            <!-- Edit trigger -->
            <button class="btn btn-warning btn-sm"
              onclick="openEdit(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">✏️ Modifier</button>
            <!-- Delete -->
            <form method="POST" style="display:inline"
                  onsubmit="return confirm('Supprimer ce vendeur ?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id"     value="<?= e($s['id']) ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">👤</div>
    <p><?= $search ? 'Aucun vendeur trouvé pour "'.e($search).'".' : 'Aucun vendeur enregistré. Cliquez sur "+ Ajouter" pour commencer.' ?></p>
  </div>
  <?php endif; ?>
</div>

<!-- ── Modal Add ──────────────────────────────────────── -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header">
      <h3>➕ Ajouter un vendeur</h3>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Prénom *</label>
            <input type="text" name="first_name" required placeholder="ex: Ahmed">
          </div>
          <div class="form-group">
            <label>Nom *</label>
            <input type="text" name="last_name" required placeholder="ex: Benali">
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required placeholder="ex: ahmed@pharma.dz">
          </div>
          <div class="form-group">
            <label>Téléphone *</label>
            <input type="text" name="phone" required placeholder="ex: 0551234567">
          </div>
          <div class="form-group full">
            <label>Adresse</label>
            <textarea name="address" placeholder="Adresse complète…"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Modal Edit ──────────────────────────────────────── -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal">
    <div class="modal-header">
      <h3>✏️ Modifier le vendeur</h3>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Prénom *</label>
            <input type="text" name="first_name" id="edit-first_name" required>
          </div>
          <div class="form-group">
            <label>Nom *</label>
            <input type="text" name="last_name" id="edit-last_name" required>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" id="edit-email" required>
          </div>
          <div class="form-group">
            <label>Téléphone *</label>
            <input type="text" name="phone" id="edit-phone" required>
          </div>
          <div class="form-group full">
            <label>Adresse</label>
            <textarea name="address" id="edit-address"></textarea>
          </div>
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
function openEdit(data) {
  ['id','first_name','last_name','email','phone','address'].forEach(k => {
    const el = document.getElementById('edit-' + k);
    if (el) el.value = data[k] || '';
  });
  document.getElementById('modal-edit').classList.add('open');
}
</script>

<?php include '../includes/footer.php'; ?>
