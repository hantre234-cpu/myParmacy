<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Nouveau médicament';
$activePage = 'medicine';

$pdo = db();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$errors = [];
$data = [
    'name'=>'','generic_name'=>'','category_id'=>'','dosage'=>'',
    'price'=>'','stock'=>0,'expiry_date'=>'','description'=>'','requires_rx'=>0
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'         => trim($_POST['name']         ?? ''),
        'generic_name' => trim($_POST['generic_name'] ?? ''),
        'category_id'  => (int)($_POST['category_id'] ?? 0) ?: null,
        'dosage'       => trim($_POST['dosage']        ?? ''),
        'price'        => (float)str_replace(',','.',  $_POST['price']  ?? 0),
        'stock'        => (int)($_POST['stock']        ?? 0),
        'expiry_date'  => trim($_POST['expiry_date']   ?? '') ?: null,
        'description'  => trim($_POST['description']   ?? ''),
        'requires_rx'  => isset($_POST['requires_rx']) ? 1 : 0,
    ];

    if (!$data['name'])       $errors['name']  = 'Nom du médicament requis.';
    if ($data['price'] <= 0)  $errors['price'] = 'Prix invalide.';
    if ($data['stock']  < 0)  $errors['stock'] = 'Stock invalide.';

    if (empty($errors)) {
        $pdo->prepare("
            INSERT INTO medicines
                (name, generic_name, category_id, dosage, price, stock, expiry_date, description, requires_rx)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['name'], $data['generic_name'], $data['category_id'],
            $data['dosage'], $data['price'], $data['stock'],
            $data['expiry_date'], $data['description'], $data['requires_rx']
        ]);

        flash('success', 'Médicament « '.$data['name'].' » ajouté avec succès.');
        redirect(BASE_URL . '/medicine/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.83rem;color:var(--clr-muted)">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/medicine/index.php" style="color:var(--clr-mint)">Médicaments</a></li>
          <li class="breadcrumb-item active" style="color:var(--clr-muted)">Nouveau</li>
        </ol>
      </nav>

      <h1 class="section-title mb-1">Nouveau médicament</h1>
      <p class="section-sub mb-4">Renseignez les informations du produit.</p>

      <div class="pc-form-wrap fade-up">
        <form method="post" novalidate>
          <div class="row g-4">

            <div class="col-md-7">
              <label class="form-label">Nom commercial *</label>
              <input type="text" name="name" value="<?= h($data['name']) ?>"
                     class="pc-input form-control <?= isset($errors['name'])?'is-invalid':'' ?>"
                     placeholder="Amoxicilline 500mg">
              <?php if (isset($errors['name'])): ?><div style="color:var(--clr-danger);font-size:.78rem;margin-top:4px"><?= h($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-5">
              <label class="form-label">Molécule / DCI</label>
              <input type="text" name="generic_name" value="<?= h($data['generic_name']) ?>"
                     class="pc-input form-control" placeholder="Amoxicilline">
            </div>

            <div class="col-md-6">
              <label class="form-label">Catégorie</label>
              <select name="category_id" class="pc-input form-select">
                <option value="">— Choisir —</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $data['category_id']==$c['id']?'selected':'' ?>>
                  <?= h($c['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Dosage</label>
              <input type="text" name="dosage" value="<?= h($data['dosage']) ?>"
                     class="pc-input form-control" placeholder="500mg, 10ml…">
            </div>

            <div class="col-md-4">
              <label class="form-label">Prix (DA) *</label>
              <input type="number" name="price" value="<?= h($data['price']) ?>"
                     min="0" step="0.01"
                     class="pc-input form-control <?= isset($errors['price'])?'is-invalid':'' ?>"
                     placeholder="0.00">
              <?php if (isset($errors['price'])): ?><div style="color:var(--clr-danger);font-size:.78rem;margin-top:4px"><?= h($errors['price']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label">Stock (unités)</label>
              <input type="number" name="stock" value="<?= h($data['stock']) ?>"
                     min="0"
                     class="pc-input form-control <?= isset($errors['stock'])?'is-invalid':'' ?>">
              <?php if (isset($errors['stock'])): ?><div style="color:var(--clr-danger);font-size:.78rem;margin-top:4px"><?= h($errors['stock']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label">Date d'expiration</label>
              <input type="date" name="expiry_date" value="<?= h($data['expiry_date'] ?? '') ?>"
                     class="pc-input form-control">
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" rows="3"
                        class="pc-input form-control"
                        placeholder="Indications, contre-indications, posologie…"><?= h($data['description']) ?></textarea>
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="requires_rx" id="requires_rx"
                       <?= $data['requires_rx'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="requires_rx" style="color:var(--clr-muted);font-size:.88rem">
                  <span class="badge-amber me-1">Rx</span> Requiert une ordonnance médicale
                </label>
              </div>
            </div>
          </div>

          <hr class="pc-divider">

          <div class="d-flex gap-3 justify-content-end">
            <a href="<?= BASE_URL ?>/medicine/index.php" class="btn-ghost">Annuler</a>
            <button type="submit" class="btn-mint"><i class="bi bi-check-lg"></i> Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
