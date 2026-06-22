<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

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
    $q = http_build_query(['msg' => implode(' ', $errores), 'tipo' => 'error']);
    header('Location: /index.php?' . $q);
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
    $msg = 'Producto registrado correctamente.';
} else {
    // Actualizar
    $st = $pdo->prepare(
        'UPDATE productos
            SET nombre = ?, categoria = ?, marca = ?, precio = ?, stock = ?, stock_min = ?, descripcion = ?
          WHERE id = ?'
    );
    $st->execute([$nombre, $categoria, $marca, $precio, $stock, $stock_min, $descripcion, $id]);
    $msg = 'Producto actualizado correctamente.';
}

$q = http_build_query(['msg' => $msg, 'tipo' => 'ok']);
header('Location: /index.php?' . $q);
exit;
