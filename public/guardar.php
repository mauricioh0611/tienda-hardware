<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

// Validar token CSRF
csrf_validate();

// --- Recolección y validación de datos ---
$id          = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
$nombre      = trim($_POST['nombre'] ?? '');
$categoria   = trim($_POST['categoria'] ?? 'General');
$marca       = trim($_POST['marca'] ?? '');
$precio      = (float) ($_POST['precio'] ?? 0);
$stock       = (int) ($_POST['stock'] ?? 0);
$stock_min   = (int) ($_POST['stock_min'] ?? 5);
$descripcion = trim($_POST['descripcion'] ?? '');

$errores = [];
if ($nombre === '')        $errores[] = 'El nombre es obligatorio.';
if ($precio < 0)           $errores[] = 'El precio no puede ser negativo.';
if ($stock < 0)            $errores[] = 'El stock no puede ser negativo.';

if ($errores) {
    flash(implode(' ', $errores), 'error');
    header('Location: /index.php');
    exit;
}

$pdo = db();

if ($id === null) {
    // Crear
    $st = $pdo->prepare(
        'INSERT INTO productos (nombre, categoria, marca, precio, stock, stock_min, descripcion)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $st->execute([$nombre, $categoria, $marca, $precio, $stock, $stock_min, $descripcion]);
    $nuevoId = (int) $pdo->lastInsertId();
    auditoria_registrar('crear', $nuevoId, "Producto: $nombre");
    flash('Producto registrado correctamente.', 'ok');
} else {
    // Verificar que el producto exista
    $st = $pdo->prepare('SELECT nombre FROM productos WHERE id = ?');
    $st->execute([$id]);
    $existente = $st->fetch();
    if (!$existente) {
        flash('Producto no encontrado.', 'error');
        header('Location: /index.php');
        exit;
    }
    // Actualizar
    $st = $pdo->prepare(
        'UPDATE productos
            SET nombre = ?, categoria = ?, marca = ?, precio = ?, stock = ?, stock_min = ?, descripcion = ?
          WHERE id = ?'
    );
    $st->execute([$nombre, $categoria, $marca, $precio, $stock, $stock_min, $descripcion, $id]);
    auditoria_registrar('editar', $id, "Producto: $nombre");
    flash('Producto actualizado correctamente.', 'ok');
}

header('Location: /index.php');
exit;
