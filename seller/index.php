<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Vendeurs';
$activePage = 'seller';

// ── Search & pagination ───────────────────────────────────────
$q    = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;

$pdo  = db();
$where = $q ? "WHERE (s.first_name LIKE :q OR s.last_name LIKE :q OR s.email LIKE :q)" : "";
$bind  = $q ? [':q' => "%$q%"] : [];

$total = $pdo->prepare("SELECT COUNT(*) FROM sellers s $where");
$total->execute($bind);
$pg = paginate((int)$total->fetchColumn(), $per, $page);

$stmt = $pdo->prepare("
    SELECT s.*, COUNT(cr.id) AS register_count
    FROM sellers s
    LEFT JOIN cash_registers cr ON cr.seller_id = s.id
    $where
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $pg['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],   PDO::PARAM_INT);
foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$sellers = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">

  <!-- Header row -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-section">
    <div>
      <h1 class="section-title">Vendeurs</h1>
      <p class="section-sub">Membres enregistrés de la pharmacie.</p>
    </div>
    <a href="<?= BASE_URL ?>/seller/create.php" class="btn-mint">
      <i class="bi bi-person-plus"></i> Nouveau vendeur
    </a>
  </div>

  <!-- Search -->
  <div class="row mb-4">
    <div class="col-md-5">
      <form method="get">
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" name="q" value="<?= h($q) ?>"
                 placeholder="Nom, prénom ou e-mail…"
                 class="pc-input form-control" id="liveSearch">
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="pc-table-wrap">
    <?php if (empty($sellers)): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-people"></i></div>
        <p>Aucun vendeur trouvé<?= $q ? " pour « <strong>".h($q)."</strong> »" : "" ?>.</p>
        <a href="<?= BASE_URL ?>/seller/create.php" class="btn-mint mt-2">Ajouter le premier vendeur</a>
      </div>
    <?php else: ?>
    <table class="pc-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom complet</th>
          <th>E-mail</th>
          <th>Téléphone</th>
          <th>Rôle</th>
          <th>Caisses</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sellers as $s): ?>
        <tr data-searchable>
          <td class="font-mono text-muted-pc"><?= (int)$s['id'] ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div style="width:34px;height:34px;border-radius:50%;background:var(--clr-mint-pale);display:grid;place-items:center;font-size:.8rem;color:var(--clr-mint);font-weight:700;flex-shrink:0">
                <?= strtoupper(mb_substr($s['first_name'],0,1).mb_substr($s['last_name'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:600"><?= h($s['first_name'].' '.$s['last_name']) ?></div>
                <div style="font-size:.75rem;color:var(--clr-muted)">depuis <?= date('d/m/Y', strtotime($s['created_at'])) ?></div>
              </div>
            </div>
          </td>
          <td><?= h($s['email']) ?></td>
          <td><?= h($s['phone'] ?: '—') ?></td>
          <td>
            <?php if ($s['role']==='admin'): ?>
              <span class="badge-mint">Admin</span>
            <?php else: ?>
              <span class="badge-muted">Vendeur</span>
            <?php endif; ?>
          </td>
          <td class="font-mono"><?= (int)$s['register_count'] ?></td>
          <td>
            <?php if ($s['is_active']): ?>
              <span class="badge-mint"><i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Actif</span>
            <?php else: ?>
              <span class="badge-rose">Inactif</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>/seller/edit.php?id=<?= $s['id'] ?>" class="btn-ghost" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?= BASE_URL ?>/seller/delete.php?id=<?= $s['id'] ?>"
                 class="btn-danger-soft"
                 data-confirm="Supprimer ce vendeur définitivement ?"
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

  <!-- Pagination -->
  <?php if ($pg['pages'] > 1): ?>
  <nav class="mt-4">
    <ul class="pagination pc-pagination justify-content-center">
      <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
      <li class="page-item <?= $i===$page?'active':'' ?>">
        <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
