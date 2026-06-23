<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Acción no permitida.', 'error');
    header('Location: /index.php');
    exit;
}

// Validar token CSRF
csrf_validate();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $pdo = db();

    // Obtener datos del producto antes de eliminar (para auditoría)
    $st = $pdo->prepare('SELECT nombre FROM productos WHERE id = ?');
    $st->execute([$id]);
    $producto = $st->fetch();

    if ($producto) {
        $st = $pdo->prepare('DELETE FROM productos WHERE id = ?');
        $st->execute([$id]);
        auditoria_registrar('eliminar', $id, "Producto: {$producto['nombre']}");
        flash('Producto eliminado correctamente.', 'ok');
    } else {
        flash('Producto no encontrado.', 'error');
    }
} else {
    flash('Identificador inválido.', 'error');
}

header('Location: /index.php');
exit;
