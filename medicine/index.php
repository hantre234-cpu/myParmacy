<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Médicaments';
$activePage = 'medicine';

$q    = trim($_GET['q']    ?? '');
$cat  = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 12;

$pdo = db();

$conditions = [];
$bind = [];

if ($q)   { $conditions[] = "(m.name LIKE :q OR m.generic_name LIKE :q)"; $bind[':q'] = "%$q%"; }
if ($cat) { $conditions[] = "m.category_id = :cat"; $bind[':cat'] = $cat; }

$where = $conditions ? "WHERE " . implode(' AND ', $conditions) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM medicines m $where");
$total->execute($bind);
$pg = paginate((int)$total->fetchColumn(), $per, $page);

$stmt = $pdo->prepare("
    SELECT m.*, c.name AS category_name
    FROM medicines m
    LEFT JOIN categories c ON c.id = m.category_id
    $where
    ORDER BY m.name ASC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $pg['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],   PDO::PARAM_INT);
foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$medicines = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">

  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-section">
    <div>
      <h1 class="section-title">Médicaments</h1>
      <p class="section-sub">Gestion du stock et des informations produits.</p>
    </div>
    <a href="<?= BASE_URL ?>/medicine/create.php" class="btn-mint">
      <i class="bi bi-plus-circle"></i> Nouveau médicament
    </a>
  </div>

  <!-- Filters -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-5">
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" name="q" value="<?= h($q) ?>"
               placeholder="Nom ou molécule…" class="pc-input form-control">
      </div>
    </div>
    <div class="col-md-3">
      <select name="cat" class="pc-input form-select" onchange="this.form.submit()">
        <option value="0">Toutes les catégories</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $cat===$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn-outline-mint">Filtrer</button>
      <?php if ($q || $cat): ?><a href="?" class="btn-ghost ms-1">Réinitialiser</a><?php endif; ?>
    </div>
  </form>

  <!-- Table -->
  <div class="pc-table-wrap">
    <?php if (empty($medicines)): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-capsule"></i></div>
        <p>Aucun médicament trouvé.</p>
        <a href="<?= BASE_URL ?>/medicine/create.php" class="btn-mint mt-2">Ajouter un médicament</a>
      </div>
    <?php else: ?>
    <table class="pc-table">
      <thead>
        <tr>
          <th>Médicament</th>
          <th>Catégorie</th>
          <th>Dosage</th>
          <th>Prix</th>
          <th>Stock</th>
          <th>Expiration</th>
          <th>Ord.</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($medicines as $m): ?>
        <?php
          $expiry     = $m['expiry_date'] ? new DateTime($m['expiry_date']) : null;
          $today      = new DateTime();
          $expSoon    = $expiry && $expiry->diff($today)->days <= 90 && $expiry > $today;
          $expired    = $expiry && $expiry <= $today;
          $lowStock   = $m['stock'] < 20;
        ?>
        <tr data-searchable>
          <td>
            <div style="font-weight:600"><?= h($m['name']) ?></div>
            <?php if ($m['generic_name']): ?>
              <div style="font-size:.75rem;color:var(--clr-muted)"><?= h($m['generic_name']) ?></div>
            <?php endif; ?>
          </td>
          <td><?= $m['category_name'] ? '<span class="badge-muted">'.h($m['category_name']).'</span>' : '—' ?></td>
          <td class="font-mono text-muted-pc"><?= h($m['dosage'] ?: '—') ?></td>
          <td class="font-mono">
            <span style="color:var(--clr-mint)"><?= number_format((float)$m['price'], 2) ?></span>
            <span style="font-size:.72rem;color:var(--clr-muted)"> DA</span>
          </td>
          <td>
            <?php if ($lowStock): ?>
              <span class="badge-rose"><i class="bi bi-exclamation me-1"></i><?= (int)$m['stock'] ?></span>
            <?php else: ?>
              <span class="badge-mint"><?= (int)$m['stock'] ?></span>
            <?php endif; ?>
          </td>
          <td class="font-mono" style="font-size:.82rem">
            <?php if (!$expiry): ?>
              <span class="text-muted-pc">—</span>
            <?php elseif ($expired): ?>
              <span class="badge-rose"><i class="bi bi-x-circle me-1"></i><?= $expiry->format('d/m/Y') ?></span>
            <?php elseif ($expSoon): ?>
              <span class="badge-amber"><i class="bi bi-clock me-1"></i><?= $expiry->format('d/m/Y') ?></span>
            <?php else: ?>
              <?= $expiry->format('d/m/Y') ?>
            <?php endif; ?>
          </td>
          <td>
            <?= $m['requires_rx'] ? '<span class="badge-amber" title="Ordonnance requise">Rx</span>' : '<span class="badge-muted">—</span>' ?>
          </td>
          <td>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>/medicine/edit.php?id=<?= $m['id'] ?>" class="btn-ghost" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?= BASE_URL ?>/medicine/delete.php?id=<?= $m['id'] ?>"
                 class="btn-danger-soft"
                 data-confirm="Supprimer « <?= h($m['name']) ?> » ?"
                 title="Supprimer">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php if ($pg['pages'] > 1): ?>
  <nav class="mt-4">
    <ul class="pagination pc-pagination justify-content-center">
      <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
      <li class="page-item <?= $i===$page?'active':'' ?>">
        <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&cat=<?= $cat ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
