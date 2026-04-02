<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Détail caisse';
$activePage = 'cashier';

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);

$register = $pdo->prepare("
    SELECT cr.*, s.first_name, s.last_name
    FROM cash_registers cr
    LEFT JOIN sellers s ON s.id = cr.seller_id
    WHERE cr.id = ?
");
$register->execute([$id]);
$register = $register->fetch();

if (!$register) { flash('error','Caisse introuvable.'); redirect(BASE_URL.'/cashier/index.php'); }

// Handle add sale
$saleErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $sData = [
        'total_amount'   => (float)str_replace(',','.', $_POST['total_amount'] ?? 0),
        'payment_method' => in_array($_POST['payment_method'] ?? '', ['cash','card','other']) ? $_POST['payment_method'] : 'cash',
        'note'           => trim($_POST['note'] ?? ''),
    ];

    if ($sData['total_amount'] <= 0) $saleErrors['total_amount'] = 'Montant invalide.';

    if (empty($saleErrors)) {
        $sellers = $pdo->query("SELECT id FROM sellers WHERE is_active=1 LIMIT 1")->fetchColumn();
        $pdo->prepare("
            INSERT INTO sales (cash_register_id, seller_id, total_amount, payment_method, note)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$id, $register['seller_id'], $sData['total_amount'], $sData['payment_method'], $sData['note']]);

        flash('success', 'Vente de '.number_format($sData['total_amount'],2).' DA enregistrée.');
        redirect(BASE_URL.'/cashier/view.php?id='.$id);
    }
}

// Load sales
$sales = $pdo->prepare("SELECT * FROM sales WHERE cash_register_id = ? ORDER BY sold_at DESC");
$sales->execute([$id]);
$sales = $sales->fetchAll();

$totalSales = array_sum(array_column($sales, 'total_amount'));

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">

  <!-- Header -->
  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-section">
    <div>
      <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb" style="font-size:.83rem;color:var(--clr-muted)">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cashier/index.php" style="color:var(--clr-mint)">Caisses</a></li>
          <li class="breadcrumb-item active" style="color:var(--clr-muted)"><?= h($register['label']) ?></li>
        </ol>
      </nav>
      <h1 class="section-title mb-1"><?= h($register['label']) ?></h1>
      <p class="section-sub">
        <?= $register['first_name'] ? h($register['first_name'].' '.$register['last_name']) : 'Vendeur non assigné' ?>
        <?php if ($register['opening_time']): ?> · Ouverture : <?= date('d/m/Y H:i', strtotime($register['opening_time'])) ?><?php endif; ?>
      </p>
    </div>
    <div class="d-flex gap-2">
      <?php if ($register['status']==='open'): ?>
        <a href="<?= BASE_URL ?>/cashier/close.php?id=<?= $id ?>" class="btn-outline-mint">
          <i class="bi bi-lock"></i> Clôturer
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-sm-4">
      <div class="pc-card text-center">
        <div class="text-muted-pc mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Montant ouverture</div>
        <div class="stat-number"><?= number_format((float)$register['amount_opening'],2) ?></div>
        <div class="text-muted-pc" style="font-size:.75rem">DA</div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="pc-card text-center">
        <div class="text-muted-pc mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Total ventes</div>
        <div class="stat-number"><?= number_format($totalSales, 2) ?></div>
        <div class="text-muted-pc" style="font-size:.75rem">DA · <?= count($sales) ?> transaction(s)</div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="pc-card text-center">
        <div class="text-muted-pc mb-1" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Statut</div>
        <div class="mt-2">
          <?= $register['status']==='open'
            ? '<span class="badge-mint" style="font-size:.9rem;padding:6px 18px"><i class="bi bi-circle-fill me-1" style="font-size:.55rem"></i>Ouverte</span>'
            : '<span class="badge-muted" style="font-size:.9rem;padding:6px 18px">Clôturée</span>' ?>
        </div>
        <?php if ($register['closing_time']): ?>
          <div class="text-muted-pc mt-1" style="font-size:.75rem">le <?= date('d/m/Y H:i', strtotime($register['closing_time'])) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-4">

    <!-- Add sale form -->
    <?php if ($register['status']==='open'): ?>
    <div class="col-lg-4">
      <div class="pc-form-wrap" style="position:sticky;top:90px">
        <h5 class="section-title mb-3" style="font-size:1.1rem">
          <i class="bi bi-plus-circle text-mint me-2"></i>Ajouter une vente
        </h5>
        <form method="post" novalidate>
          <input type="hidden" name="add_sale" value="1">
          <div class="mb-3">
            <label class="form-label">Montant (DA) *</label>
            <input type="number" name="total_amount" min="0.01" step="0.01"
                   class="pc-input form-control <?= isset($saleErrors['total_amount'])?'is-invalid':'' ?>"
                   placeholder="0.00">
            <?php if (isset($saleErrors['total_amount'])): ?><div style="color:var(--clr-danger);font-size:.78rem;margin-top:4px"><?= h($saleErrors['total_amount']) ?></div><?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Mode de paiement</label>
            <select name="payment_method" class="pc-input form-select">
              <option value="cash">Espèces</option>
              <option value="card">Carte bancaire</option>
              <option value="other">Autre</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label">Note (optionnel)</label>
            <textarea name="note" rows="2" class="pc-input form-control" placeholder="Produits vendus, client…"></textarea>
          </div>
          <button type="submit" class="btn-mint w-100">
            <i class="bi bi-plus-lg"></i> Enregistrer la vente
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Sales list -->
    <div class="<?= $register['status']==='open' ? 'col-lg-8' : 'col-12' ?>">
      <h5 class="section-title mb-3" style="font-size:1.1rem">Transactions (<?= count($sales) ?>)</h5>

      <div class="pc-table-wrap">
        <?php if (empty($sales)): ?>
          <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-receipt"></i></div>
            <p>Aucune vente enregistrée.</p>
          </div>
        <?php else: ?>
        <table class="pc-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Date / Heure</th>
              <th>Montant</th>
              <th>Paiement</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sales as $sale): ?>
            <tr>
              <td class="font-mono text-muted-pc"><?= (int)$sale['id'] ?></td>
              <td class="font-mono" style="font-size:.82rem"><?= date('d/m/Y H:i', strtotime($sale['sold_at'])) ?></td>
              <td class="font-mono" style="color:var(--clr-mint);font-weight:600"><?= number_format((float)$sale['total_amount'],2) ?> DA</td>
              <td>
                <?php
                  $pm = ['cash'=>['Espèces','badge-mint'], 'card'=>['Carte','badge-blue'], 'other'=>['Autre','badge-muted']];
                  [$label,$badge] = $pm[$sale['payment_method']] ?? ['—','badge-muted'];
                ?>
                <span class="<?= $badge ?>"><?= $label ?></span>
              </td>
              <td style="font-size:.82rem;color:var(--clr-muted)"><?= h($sale['note'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
