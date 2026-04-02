<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Nouvelle caisse';
$activePage = 'cashier';

$pdo     = db();
$sellers = $pdo->query("SELECT id, first_name, last_name FROM sellers WHERE is_active=1 ORDER BY first_name")->fetchAll();

$errors = [];
$data = ['label'=>'','seller_id'=>'','amount_opening'=>0,'opening_time'=>date('Y-m-d\TH:i')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'label'          => trim($_POST['label']          ?? ''),
        'seller_id'      => (int)($_POST['seller_id']     ?? 0) ?: null,
        'amount_opening' => (float)str_replace(',','.', $_POST['amount_opening'] ?? 0),
        'opening_time'   => trim($_POST['opening_time']   ?? '') ?: null,
    ];

    if (!$data['label'])          $errors['label']  = 'Libellé requis.';
    if ($data['amount_opening'] < 0) $errors['amount_opening'] = 'Montant invalide.';

    if (empty($errors)) {
        $pdo->prepare("
            INSERT INTO cash_registers (label, seller_id, amount_opening, opening_time, status)
            VALUES (?, ?, ?, ?, 'open')
        ")->execute([$data['label'], $data['seller_id'], $data['amount_opening'], $data['opening_time']]);

        flash('success', 'Caisse « '.$data['label'].' » ouverte avec succès.');
        redirect(BASE_URL . '/cashier/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">
  <div class="row justify-content-center">
    <div class="col-lg-6">

      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.83rem;color:var(--clr-muted)">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cashier/index.php" style="color:var(--clr-mint)">Caisses</a></li>
          <li class="breadcrumb-item active" style="color:var(--clr-muted)">Nouvelle</li>
        </ol>
      </nav>

      <h1 class="section-title mb-1">Ouvrir une caisse</h1>
      <p class="section-sub mb-4">Enregistrez une nouvelle session de caisse.</p>

      <div class="pc-form-wrap fade-up">
        <form method="post" novalidate>
          <div class="row g-4">

            <div class="col-12">
              <label class="form-label">Libellé *</label>
              <input type="text" name="label" value="<?= h($data['label']) ?>"
                     class="pc-input form-control <?= isset($errors['label'])?'is-invalid':'' ?>"
                     placeholder="Caisse N°1, Caisse Principal…">
              <?php if (isset($errors['label'])): ?><div style="color:var(--clr-danger);font-size:.78rem;margin-top:4px"><?= h($errors['label']) ?></div><?php endif; ?>
            </div>

            <div class="col-12">
              <label class="form-label">Vendeur responsable</label>
              <select name="seller_id" class="pc-input form-select">
                <option value="">— Non assigné —</option>
                <?php foreach ($sellers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $data['seller_id']==$s['id']?'selected':'' ?>>
                  <?= h($s['first_name'].' '.$s['last_name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-sm-6">
              <label class="form-label">Montant à l'ouverture (DA)</label>
              <input type="number" name="amount_opening" value="<?= h($data['amount_opening']) ?>"
                     min="0" step="0.01"
                     class="pc-input form-control <?= isset($errors['amount_opening'])?'is-invalid':'' ?>">
              <?php if (isset($errors['amount_opening'])): ?><div style="color:var(--clr-danger);font-size:.78rem;margin-top:4px"><?= h($errors['amount_opening']) ?></div><?php endif; ?>
            </div>

            <div class="col-sm-6">
              <label class="form-label">Heure d'ouverture</label>
              <input type="datetime-local" name="opening_time" value="<?= h($data['opening_time']) ?>"
                     class="pc-input form-control">
            </div>

          </div>

          <hr class="pc-divider">

          <div class="d-flex gap-3 justify-content-end">
            <a href="<?= BASE_URL ?>/cashier/index.php" class="btn-ghost">Annuler</a>
            <button type="submit" class="btn-mint"><i class="bi bi-unlock"></i> Ouvrir la caisse</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
