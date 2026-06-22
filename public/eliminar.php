<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $pdo = db();
    $st = $pdo->prepare('DELETE FROM productos WHERE id = ?');
    $st->execute([$id]);
    $msg = 'Producto eliminado correctamente.';
    $tipo = 'ok';
} else {
    $msg = 'Identificador inválido.';
    $tipo = 'error';
}

header('Location: /index.php?' . http_build_query(['msg' => $msg, 'tipo' => $tipo]));
exit;
