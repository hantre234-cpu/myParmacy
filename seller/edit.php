<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Modifier vendeur';
$activePage = 'seller';

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);

$seller = $pdo->prepare("SELECT * FROM sellers WHERE id = ?");
$seller->execute([$id]);
$seller = $seller->fetch();

if (!$seller) {
    flash('error', 'Vendeur introuvable.');
    redirect(BASE_URL . '/seller/index.php');
}

$errors = [];
$data   = $seller;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name']  ?? ''),
        'email'      => trim($_POST['email']       ?? ''),
        'phone'      => trim($_POST['phone']       ?? ''),
        'role'       => in_array($_POST['role'] ?? '', ['admin','seller']) ? $_POST['role'] : 'seller',
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        'password'   => $_POST['password'] ?? '',
    ];

    if (!$data['first_name']) $errors['first_name'] = 'Prénom requis.';
    if (!$data['last_name'])  $errors['last_name']  = 'Nom requis.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'E-mail invalide.';
    if ($data['password'] && strlen($data['password']) < 8) $errors['password'] = 'Mot de passe ≥ 8 caractères.';

    if (empty($errors)) {
        $exist = $pdo->prepare("SELECT id FROM sellers WHERE email = ? AND id != ?");
        $exist->execute([$data['email'], $id]);
        if ($exist->fetch()) {
            $errors['email'] = 'Cet e-mail est déjà utilisé par un autre vendeur.';
        } else {
            $passClause = $data['password'] ? ", password = ?" : "";
            $params = [
                $data['first_name'], $data['last_name'], $data['email'],
                $data['phone'], $data['role'], $data['is_active'],
            ];
            if ($data['password']) $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
            $params[] = $id;

            $pdo->prepare("
                UPDATE sellers
                SET first_name=?, last_name=?, email=?, phone=?, role=?, is_active=?
                $passClause
                WHERE id=?
            ")->execute($params);

            flash('success', 'Vendeur mis à jour avec succès.');
            redirect(BASE_URL . '/seller/index.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.83rem;color:var(--clr-muted)">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/seller/index.php" style="color:var(--clr-mint)">Vendeurs</a></li>
          <li class="breadcrumb-item active" style="color:var(--clr-muted)">Modifier</li>
        </ol>
      </nav>

      <h1 class="section-title mb-1">Modifier le vendeur</h1>
      <p class="section-sub mb-4"><?= h($seller['first_name'].' '.$seller['last_name']) ?></p>

      <div class="pc-form-wrap fade-up">
        <form method="post" novalidate>
          <div class="row g-4">

            <div class="col-sm-6">
              <label class="form-label">Prénom</label>
              <input type="text" name="first_name" value="<?= h($data['first_name']) ?>"
                     class="pc-input form-control <?= isset($errors['first_name'])?'is-invalid':'' ?>">
              <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['first_name']) ?></div><?php endif; ?>
            </div>

            <div class="col-sm-6">
              <label class="form-label">Nom</label>
              <input type="text" name="last_name" value="<?= h($data['last_name']) ?>"
                     class="pc-input form-control <?= isset($errors['last_name'])?'is-invalid':'' ?>">
              <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['last_name']) ?></div><?php endif; ?>
            </div>

            <div class="col-sm-8">
              <label class="form-label">E-mail</label>
              <input type="email" name="email" value="<?= h($data['email']) ?>"
                     class="pc-input form-control <?= isset($errors['email'])?'is-invalid':'' ?>">
              <?php if (isset($errors['email'])): ?><div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['email']) ?></div><?php endif; ?>
            </div>

            <div class="col-sm-4">
              <label class="form-label">Téléphone</label>
              <input type="tel" name="phone" value="<?= h($data['phone']) ?>" class="pc-input form-control">
            </div>

            <div class="col-sm-6">
              <label class="form-label">Rôle</label>
              <select name="role" class="pc-input form-select">
                <option value="seller" <?= $data['role']==='seller'?'selected':'' ?>>Vendeur</option>
                <option value="admin"  <?= $data['role']==='admin' ?'selected':'' ?>>Admin</option>
              </select>
            </div>

            <div class="col-sm-6 d-flex align-items-end">
              <div class="form-check" style="margin-bottom:4px">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                       <?= $data['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active" style="color:var(--clr-muted);font-size:.85rem">
                  Compte actif
                </label>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Nouveau mot de passe <small style="font-weight:400;text-transform:none">(laisser vide pour ne pas changer)</small></label>
              <input type="password" name="password"
                     class="pc-input form-control <?= isset($errors['password'])?'is-invalid':'' ?>"
                     placeholder="Min. 8 caractères">
              <?php if (isset($errors['password'])): ?><div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['password']) ?></div><?php endif; ?>
            </div>
          </div>

          <hr class="pc-divider">

          <div class="d-flex gap-3 justify-content-end">
            <a href="<?= BASE_URL ?>/seller/index.php" class="btn-ghost">Annuler</a>
            <button type="submit" class="btn-mint"><i class="bi bi-check-lg"></i> Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
