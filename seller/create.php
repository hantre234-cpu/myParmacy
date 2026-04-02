<?php
require_once __DIR__ . '/../includes/config.php';

$pageTitle  = 'Nouveau vendeur';
$activePage = 'seller';

$errors = [];
$data   = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>'','role'=>'seller'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name']  ?? ''),
        'email'      => trim($_POST['email']       ?? ''),
        'phone'      => trim($_POST['phone']       ?? ''),
        'role'       => in_array($_POST['role'] ?? '', ['admin','seller']) ? $_POST['role'] : 'seller',
        'password'   => $_POST['password'] ?? '',
        'password2'  => $_POST['password2'] ?? '',
    ];

    if (!$data['first_name']) $errors['first_name'] = 'Prénom requis.';
    if (!$data['last_name'])  $errors['last_name']  = 'Nom requis.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'E-mail invalide.';
    if (strlen($data['password']) < 8) $errors['password'] = 'Mot de passe ≥ 8 caractères.';
    if ($data['password'] !== $data['password2']) $errors['password2'] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $pdo = db();
        $exist = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $exist->execute([$data['email']]);
        if ($exist->fetch()) {
            $errors['email'] = 'Cet e-mail est déjà utilisé.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO sellers (first_name, last_name, email, phone, password, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['first_name'], $data['last_name'],
                $data['email'], $data['phone'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['role'],
            ]);
            flash('success', 'Vendeur « '.$data['first_name'].' '.$data['last_name'].' » ajouté avec succès.');
            redirect(BASE_URL . '/seller/index.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-xl section-pad">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.83rem;color:var(--clr-muted)">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/seller/index.php" style="color:var(--clr-mint)">Vendeurs</a></li>
          <li class="breadcrumb-item active" style="color:var(--clr-muted)">Nouveau</li>
        </ol>
      </nav>

      <h1 class="section-title mb-1">Nouveau vendeur</h1>
      <p class="section-sub mb-4">Remplissez le formulaire pour ajouter un membre.</p>

      <div class="pc-form-wrap fade-up">
        <form method="post" novalidate>

          <div class="row g-4">
            <div class="col-sm-6">
              <label class="form-label">Prénom</label>
              <input type="text" name="first_name" value="<?= h($data['first_name']) ?>"
                     class="pc-input form-control <?= isset($errors['first_name'])?'is-invalid':'' ?>"
                     placeholder="Karim">
              <?php if (isset($errors['first_name'])): ?>
                <div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['first_name']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-sm-6">
              <label class="form-label">Nom</label>
              <input type="text" name="last_name" value="<?= h($data['last_name']) ?>"
                     class="pc-input form-control <?= isset($errors['last_name'])?'is-invalid':'' ?>"
                     placeholder="Benali">
              <?php if (isset($errors['last_name'])): ?>
                <div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['last_name']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-sm-8">
              <label class="form-label">E-mail</label>
              <input type="email" name="email" value="<?= h($data['email']) ?>"
                     class="pc-input form-control <?= isset($errors['email'])?'is-invalid':'' ?>"
                     placeholder="vendeur@exemple.com">
              <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['email']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-sm-4">
              <label class="form-label">Téléphone</label>
              <input type="tel" name="phone" value="<?= h($data['phone']) ?>"
                     class="pc-input form-control" placeholder="0555…">
            </div>

            <div class="col-sm-6">
              <label class="form-label">Mot de passe</label>
              <input type="password" name="password"
                     class="pc-input form-control <?= isset($errors['password'])?'is-invalid':'' ?>"
                     placeholder="Min. 8 caractères">
              <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['password']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-sm-6">
              <label class="form-label">Confirmer</label>
              <input type="password" name="password2"
                     class="pc-input form-control <?= isset($errors['password2'])?'is-invalid':'' ?>"
                     placeholder="Répéter le mot de passe">
              <?php if (isset($errors['password2'])): ?>
                <div class="invalid-feedback" style="color:var(--clr-danger);font-size:.78rem"><?= h($errors['password2']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-sm-6">
              <label class="form-label">Rôle</label>
              <select name="role" class="pc-input form-select">
                <option value="seller" <?= $data['role']==='seller'?'selected':'' ?>>Vendeur</option>
                <option value="admin"  <?= $data['role']==='admin' ?'selected':'' ?>>Admin</option>
              </select>
            </div>
          </div>

          <hr class="pc-divider">

          <div class="d-flex gap-3 justify-content-end">
            <a href="<?= BASE_URL ?>/seller/index.php" class="btn-ghost">Annuler</a>
            <button type="submit" class="btn-mint">
              <i class="bi bi-person-check"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
