<?php
require_once __DIR__ . '/../includes/config.php';

$id  = (int)($_GET['id'] ?? 0);
$pdo = db();

$s = $pdo->prepare("SELECT first_name, last_name FROM sellers WHERE id = ?");
$s->execute([$id]);
$s = $s->fetch();

if ($s) {
    $pdo->prepare("DELETE FROM sellers WHERE id = ?")->execute([$id]);
    flash('success', 'Vendeur « '.$s['first_name'].' '.$s['last_name'].' » supprimé.');
} else {
    flash('error', 'Vendeur introuvable.');
}

redirect(BASE_URL . '/seller/index.php');
