<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Caisses & Ventes';
$activePage = 'cashier';

$pdo   = db();
$page  = max(1, (int)($_GET['page'] ?? 1));
$per   = 10;

$total = (int)$pdo->query("SELECT COUNT(*) FROM cash_registers")->fetchColumn();
$pg    = paginate($total, $per, $page);

$registers = $pdo->prepare("
    SELECT cr.*,
           s.first_name, s.last_name,
           COUNT(sa.id) AS sale_count,
           COALESCE(SUM(sa.total_amount),0) AS total_sales
    FROM cash_registers cr
    LEFT JOIN sellers s  ON s.id  = cr.seller_id
    LEFT JOIN sales   sa ON sa.cash_register_id = cr.id
    GROUP BY cr.id
    ORDER BY cr.created_at DESC
    LIMIT :limit OFFSET :offset
");
$registers->bindValue(':limit',  $pg['per_page'], PDO::PARAM_INT);
$registers->bindValue(':offset', $pg['offset'],   PDO::PARAM_INT);
$registers->execute();
$registers = $registers->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">

  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-section">
    <div>
      <h1 class="section-title">Caisses & Ventes</h1>
      <p class="section-sub">Gestion des caisses enregistreuses et des transactions.</p>
    </div>
    <a href="<?= BASE_URL ?>/cashier/create.php" class="btn-mint">
      <i class="bi bi-plus-circle"></i> Nouvelle caisse
    </a>
  </div>

  <div class="pc-table-wrap">
    <?php if (empty($registers)): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-cash-register"></i></div>
        <p>Aucune caisse créée.</p>
        <a href="<?= BASE_URL ?>/cashier/create.php" class="btn-mint mt-2">Créer la première caisse</a>
      </div>
    <?php else: ?>
    <table class="pc-table">
      <thead>
        <tr>
          <th>Caisse</th>
          <th>Vendeur</th>
          <th>Ouverture</th>
          <th>Clôture</th>
          <th>Mt. ouverture</th>
          <th>Mt. clôture</th>
          <th>Ventes</th>
          <th>Total CA</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registers as $r): ?>
        <tr>
          <td style="font-weight:600"><?= h($r['label']) ?></td>
          <td>
            <?= $r['first_name']
              ? h($r['first_name'].' '.$r['last_name'])
              : '<span class="text-muted-pc">—</span>' ?>
          </td>
          <td class="font-mono" style="font-size:.8rem">
            <?= $r['opening_time'] ? date('d/m/Y H:i', strtotime($r['opening_time'])) : '—' ?>
          </td>
          <td class="font-mono" style="font-size:.8rem">
            <?= $r['closing_time'] ? date('d/m/Y H:i', strtotime($r['closing_time'])) : '—' ?>
          </td>
          <td class="font-mono"><?= number_format((float)$r['amount_opening'],2) ?> DA</td>
          <td class="font-mono"><?= number_format((float)$r['amount_closing'],2) ?> DA</td>
          <td class="font-mono text-muted-pc"><?= (int)$r['sale_count'] ?></td>
          <td class="font-mono" style="color:var(--clr-mint)"><?= number_format((float)$r['total_sales'],2) ?> DA</td>
          <td>
            <?= $r['status']==='open'
              ? '<span class="badge-mint"><i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Ouverte</span>'
              : '<span class="badge-muted">Fermée</span>' ?>
          </td>
          <td>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>/cashier/view.php?id=<?= $r['id'] ?>" class="btn-ghost" title="Voir les ventes">
                <i class="bi bi-eye"></i>
              </a>
              <?php if ($r['status']==='open'): ?>
              <a href="<?= BASE_URL ?>/cashier/close.php?id=<?= $r['id'] ?>" class="btn-outline-mint" style="font-size:.8rem;padding:5px 12px">
                <i class="bi bi-lock"></i> Clôturer
              </a>
              <?php endif; ?>
              <a href="<?= BASE_URL ?>/cashier/delete.php?id=<?= $r['id'] ?>"
                 class="btn-danger-soft"
                 data-confirm="Supprimer cette caisse et toutes ses ventes ?"
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
