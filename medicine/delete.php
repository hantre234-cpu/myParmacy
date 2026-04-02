<?php
require_once __DIR__ . '/../includes/config.php';

$id  = (int)($_GET['id'] ?? 0);
$pdo = db();

$m = $pdo->prepare("SELECT name FROM medicines WHERE id = ?");
$m->execute([$id]);
$m = $m->fetch();

if ($m) {
    $pdo->prepare("DELETE FROM medicines WHERE id = ?")->execute([$id]);
    flash('success', 'Médicament « '.$m['name'].' » supprimé.');
} else {
    flash('error', 'Médicament introuvable.');
}

redirect(BASE_URL . '/medicine/index.php');
