<?php
require __DIR__ . '/../src/db.php';

requiere_autenticacion();

$pdo = db();

// ============================================================
// KPIs — métricas del dashboard
// ============================================================
$totalProductos = (int) $pdo->query('SELECT COUNT(*) AS c FROM productos')->fetch()['c'];
$stockBajo      = (int) $pdo->query('SELECT COUNT(*) AS c FROM productos WHERE stock <= stock_min')->fetch()['c'];
$valorTotal     = (float) $pdo->query('SELECT COALESCE(SUM(precio * stock), 0) AS v FROM productos')->fetch()['v'];

// ============================================================
// Paginación y búsqueda
// ============================================================
$pagina     = max(1, (int) ($_GET['p'] ?? 1));
$porPagina  = 10;
$offset     = ($pagina - 1) * $porPagina;

$busqueda   = trim($_GET['q'] ?? '');
$categoriaFiltro = trim($_GET['cat'] ?? '');

$where  = [];
$params = [];

if ($busqueda !== '') {
    $where[]  = '(nombre LIKE ? OR marca LIKE ? OR descripcion LIKE ?)';
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
if ($categoriaFiltro !== '') {
    $where[]  = 'categoria = ?';
    $params[] = $categoriaFiltro;
}

$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Total de resultados (para paginación)
$stCount = $pdo->prepare("SELECT COUNT(*) AS c FROM productos $sqlWhere");
$stCount->execute($params);
$totalResultados = (int) $stCount->fetch()['c'];
$totalPaginas    = max(1, (int) ceil($totalResultados / $porPagina));

// Productos de la página actual
$sql = "SELECT * FROM productos $sqlWhere ORDER BY creado_en DESC, id DESC LIMIT ? OFFSET ?";
$params[] = $porPagina;
$params[] = $offset;
$st = $pdo->prepare($sql);
$st->execute($params);
$productos = $st->fetchAll();

// Categorías para el filtro y formulario
$categorias = ['Procesadores', 'Tarjetas Gráficas', 'Memorias RAM', 'Almacenamiento',
               'Fuentes de Poder', 'Tarjetas Madre', 'Refrigeración', 'Periféricos', 'General'];

// Mensaje flash
$flash = flash_recuperar();
$msg   = $flash['msg'];
$tipo  = $flash['tipo'];
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

    <!-- ====================== KPIs ====================== -->
    <section class="kpis">
        <div class="kpi-card">
            <span class="kpi-valor"><?= $totalProductos ?></span>
            <span class="kpi-label">Productos</span>
        </div>
        <div class="kpi-card kpi-peligro">
            <span class="kpi-valor"><?= $stockBajo ?></span>
            <span class="kpi-label">Stock Bajo</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-valor"><?= precio_cop($valorTotal) ?></span>
            <span class="kpi-label">Valor Inventario</span>
        </div>
    </section>

    <!-- ====================== LISTADO ====================== -->
    <section class="tarjeta">
        <div class="listado-header">
            <h2>Inventario de Productos</h2>
            <!-- Formulario de búsqueda -->
            <form class="buscador" method="get">
                <input type="text" name="q" placeholder="Buscar producto, marca..." value="<?= e($busqueda) ?>">
                <select name="cat">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= e($c) ?>" <?= $c === $categoriaFiltro ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-verde btn-sm">Buscar</button>
                <?php if ($busqueda !== '' || $categoriaFiltro !== ''): ?>
                    <a href="/index.php" class="btn btn-gris btn-sm">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrapper">
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
                    <tr><td colspan="6">No se encontraron productos.</td></tr>
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
                                <form method="post" action="/eliminar.php" style="display:inline"
                                      onsubmit="return confirm('¿Eliminar «<?= e($p['nombre']) ?>»?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-rojo btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacion">
            <?php if ($pagina > 1): ?>
                <a href="?p=<?= $pagina - 1 ?>&q=<?= urlencode($busqueda) ?>&cat=<?= urlencode($categoriaFiltro) ?>" class="btn btn-gris btn-sm">← Anterior</a>
            <?php endif; ?>
            <span class="pagina-info">Página <?= $pagina ?> de <?= $totalPaginas ?> (<?= $totalResultados ?> resultados)</span>
            <?php if ($pagina < $totalPaginas): ?>
                <a href="?p=<?= $pagina + 1 ?>&q=<?= urlencode($busqueda) ?>&cat=<?= urlencode($categoriaFiltro) ?>" class="btn btn-gris btn-sm">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- ====================== FORMULARIO ====================== -->
    <section class="tarjeta">
        <h2>Registrar Producto</h2>
        <form action="/guardar.php" method="post">
            <?= csrf_field() ?>

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
