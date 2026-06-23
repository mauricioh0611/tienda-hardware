<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

$id = (int) ($_GET['id'] ?? 0);
$pdo = db();
$st = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
$st->execute([$id]);
$p = $st->fetch();

if (!$p) {
    flash('Producto no encontrado.', 'error');
    header('Location: /index.php');
    exit;
}

$categorias = ['Procesadores', 'Tarjetas Gráficas', 'Memorias RAM', 'Almacenamiento',
               'Fuentes de Poder', 'Tarjetas Madre', 'Refrigeración', 'Periféricos', 'General'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto - Tienda de Hardware</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<header class="navbar">
    <span class="logo"></span>
    <h1>Sistema de Gestión - Tienda de Hardware</h1>
    <div class="navbar-right">
        <span class="navbar-user"><?= e($_SESSION['usuario_nombre']) ?></span>
        <a class="btn-outline" href="/logout.php">Cerrar Sesión</a>
    </div>
</header>

<main class="contenedor">
    <section class="tarjeta">
        <h2>Editar Producto</h2>
        <form action="/guardar.php" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">

            <div class="fila">
                <div>
                    <label for="nombre">Nombre del producto *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="120" value="<?= e($p['nombre']) ?>">
                </div>
                <div>
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" maxlength="60" value="<?= e($p['marca']) ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= e($c) ?>" <?= $c === $p['categoria'] ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="precio">Precio (COP) *</label>
                    <input type="number" id="precio" name="precio" min="0" step="any" required value="<?= e((string) $p['precio']) ?>">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label for="stock">Stock (unidades) *</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" required value="<?= (int) $p['stock'] ?>">
                </div>
                <div>
                    <label for="stock_min">Stock mínimo (alerta)</label>
                    <input type="number" id="stock_min" name="stock_min" min="0" step="1" value="<?= (int) $p['stock_min'] ?>">
                </div>
            </div>

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" maxlength="500"><?= e($p['descripcion']) ?></textarea>

            <div class="mt acciones">
                <button type="submit" class="btn btn-verde">Actualizar Producto</button>
                <a href="/index.php" class="btn btn-gris">Cancelar</a>
            </div>
        </form>
    </section>
</main>

<footer>
    Trabajo Colaborativo - Grupo 5 · Politécnico Grancolombiano · 2026
</footer>

</body>
</html>
