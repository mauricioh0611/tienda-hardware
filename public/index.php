<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

$pdo = db();
$productos = $pdo->query('SELECT * FROM productos ORDER BY creado_en DESC, id DESC')->fetchAll();

// Mensajes de retroalimentación
$msg = $_GET['msg'] ?? '';
$tipo = $_GET['tipo'] ?? 'ok';

// Categorías sugeridas para el selector
$categorias = ['Procesadores', 'Tarjetas Gráficas', 'Memorias RAM', 'Almacenamiento',
               'Fuentes de Poder', 'Tarjetas Madre', 'Refrigeración', 'Periféricos', 'General'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Tienda de Hardware</title>
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

    <?php if ($msg): ?>
        <div class="alerta alerta-<?= e($tipo) ?>"><?= e($msg) ?></div>
    <?php endif; ?>

    <!-- ====================== LISTADO ====================== -->
    <section class="tarjeta">
        <h2>Inventario de Productos</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$productos): ?>
                <tr><td colspan="6">No hay productos registrados todavía.</td></tr>
            <?php else: foreach ($productos as $p): ?>
                <tr>
                    <td><strong><?= e($p['nombre']) ?></strong></td>
                    <td><?= e($p['categoria']) ?></td>
                    <td><?= e($p['marca']) ?: '—' ?></td>
                    <td><?= e(precio_cop((float) $p['precio'])) ?></td>
                    <td>
                        <?php if ((int) $p['stock'] <= (int) $p['stock_min']): ?>
                            <span class="badge badge-bajo"><?= (int) $p['stock'] ?> · Bajo</span>
                        <?php else: ?>
                            <span class="badge badge-ok"><?= (int) $p['stock'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="acciones">
                            <a class="btn btn-gris btn-sm" href="/editar.php?id=<?= (int) $p['id'] ?>">Editar</a>
                            <a class="btn btn-rojo btn-sm"
                               href="/eliminar.php?id=<?= (int) $p['id'] ?>"
                               onclick="return confirm('¿Eliminar este producto?');">Eliminar</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </section>

    <!-- ====================== FORMULARIO ====================== -->
    <section class="tarjeta">
        <h2>Registrar Producto</h2>
        <form action="/guardar.php" method="post">
            <div class="fila">
                <div>
                    <label for="nombre">Nombre del producto *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="120">
                </div>
                <div>
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" maxlength="60">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= e($c) ?>"><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="precio">Precio (COP) *</label>
                    <input type="number" id="precio" name="precio" min="0" step="any" required>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label for="stock">Stock (unidades) *</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" required>
                </div>
                <div>
                    <label for="stock_min">Stock mínimo (alerta)</label>
                    <input type="number" id="stock_min" name="stock_min" min="0" step="1" value="5">
                </div>
            </div>

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" maxlength="500"></textarea>

            <div class="mt">
                <button type="submit" class="btn btn-verde">Guardar Producto</button>
            </div>
        </form>
    </section>

</main>

<footer>
    Trabajo Colaborativo - Grupo 5 · Politécnico Grancolombiano · 2026
</footer>

</body>
</html>
