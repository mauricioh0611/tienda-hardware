<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/layout.php';

requiere_autenticacion();

$pdo = db();
$seccion = $_GET['seccion'] ?? 'dashboard';

// ============================================================
// KPIs (para todas las vistas)
// ============================================================
$totalProductos = (int) $pdo->query('SELECT COUNT(*) AS c FROM productos')->fetch()['c'];
$stockBajo      = (int) $pdo->query('SELECT COUNT(*) AS c FROM productos WHERE stock <= stock_min')->fetch()['c'];
$valorTotal     = (float) $pdo->query('SELECT COALESCE(SUM(precio * stock), 0) AS v FROM productos')->fetch()['v'];
$totalCategorias = (int) $pdo->query('SELECT COUNT(DISTINCT categoria) AS c FROM productos')->fetch()['c'];

// ============================================================
// Paginación y búsqueda (vista productos)
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

$stCount = $pdo->prepare("SELECT COUNT(*) AS c FROM productos $sqlWhere");
$stCount->execute($params);
$totalResultados = (int) $stCount->fetch()['c'];
$totalPaginas    = max(1, (int) ceil($totalResultados / $porPagina));

$sql = "SELECT * FROM productos $sqlWhere ORDER BY creado_en DESC, id DESC LIMIT ? OFFSET ?";
$params[] = $porPagina;
$params[] = $offset;
$st = $pdo->prepare($sql);
$st->execute($params);
$productos = $st->fetchAll();

// Categorías
$categorias = ['Procesadores', 'Tarjetas Gráficas', 'Memorias RAM', 'Almacenamiento',
               'Fuentes de Poder', 'Tarjetas Madre', 'Refrigeración', 'Periféricos', 'General'];

// Productos recientes para el dashboard
$recientes = $pdo->query('SELECT * FROM productos ORDER BY creado_en DESC, id DESC LIMIT 5')->fetchAll();

// Productos con stock bajo
$bajos = $pdo->query('SELECT * FROM productos WHERE stock <= stock_min ORDER BY stock ASC LIMIT 5')->fetchAll();

// Productos por categoría (para gráfico de barras)
$catStats = $pdo->query('SELECT categoria, COUNT(*) AS total FROM productos GROUP BY categoria ORDER BY total DESC')->fetchAll();

obtener_header('Dashboard', $seccion);
?>

<!-- ============================================================ -->
<!-- VISTA: DASHBOARD -->
<!-- ============================================================ -->
<?php if ($seccion === 'dashboard'): ?>

    <!-- KPIs -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-2xl font-bold text-gray-900"><?= $totalProductos ?></span>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Productos</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-2xl font-bold text-gray-900"><?= $stockBajo ?></span>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Stock Bajo</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-2xl font-bold text-gray-900"><?= precio_cop($valorTotal) ?></span>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Valor Inventario</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-2xl font-bold text-gray-900"><?= $totalCategorias ?></span>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Categorías</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Gráfico de categorías + Stock bajo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- Productos por categoría -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Productos por Categoría</h3>
            <div class="space-y-3">
                <?php
                $maxCat = $catStats[0]['total'] ?? 1;
                foreach ($catStats as $cs):
                    $porcentaje = round(($cs['total'] / $maxCat) * 100);
                ?>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-gray-700"><?= e($cs['categoria']) ?></span>
                        <span class="text-gray-500"><?= (int) $cs['total'] ?> productos</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#2E9E2E] rounded-full transition-all" style="width: <?= $porcentaje ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (!$catStats): ?>
                    <p class="text-xs text-gray-400 text-center py-4">No hay productos registrados.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Productos con stock bajo -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900">Alertas de Stock Bajo</h3>
                <?php if ($stockBajo > 5): ?>
                    <a href="/index.php?seccion=productos" class="text-xs font-semibold text-[#198754] hover:text-[#157347] transition">Ver todos →</a>
                <?php endif; ?>
            </div>
            <div class="space-y-3">
                <?php if ($bajos): foreach ($bajos as $b): ?>
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate"><?= e($b['nombre']) ?></p>
                        <p class="text-xs text-gray-500"><?= e($b['marca']) ?> · Stock mín: <?= (int) $b['stock_min'] ?></p>
                    </div>
                    <span class="ml-3 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex-shrink-0"><?= (int) $b['stock'] ?> uds.</span>
                </div>
                <?php endforeach; else: ?>
                    <p class="text-xs text-gray-400 text-center py-4">✓ Todos los productos tienen stock suficiente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Productos recientes -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900">Productos Registrados Recientemente</h3>
            <a href="/index.php?seccion=productos" class="text-xs font-semibold text-[#198754] hover:text-[#157347] transition">Ver inventario completo →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="text-left py-3 px-3">Producto</th>
                        <th class="text-left py-3 px-3 hidden sm:table-cell">Categoría</th>
                        <th class="text-left py-3 px-3">Precio</th>
                        <th class="text-left py-3 px-3">Stock</th>
                        <th class="text-left py-3 px-3">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php if (!$recientes): ?>
                    <tr><td colspan="5" class="py-8 text-center text-gray-400">No hay productos registrados.</td></tr>
                <?php else: foreach ($recientes as $r): ?>
                    <tr class="hover:bg-green-50/40 transition">
                        <td class="py-3 px-3 font-medium"><?= e($r['nombre']) ?></td>
                        <td class="py-3 px-3 text-gray-600 hidden sm:table-cell"><?= e($r['categoria']) ?></td>
                        <td class="py-3 px-3 font-mono text-sm"><?= precio_cop((float) $r['precio']) ?></td>
                        <td class="py-3 px-3">
                            <?php if ((int) $r['stock'] <= (int) $r['stock_min']): ?>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"><?= (int) $r['stock'] ?></span>
                            <?php else: ?>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700"><?= (int) $r['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3">
                            <a href="/editar.php?id=<?= (int) $r['id'] ?>" class="text-xs font-semibold text-[#198754] hover:text-[#157347] transition">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- ============================================================ -->
<!-- VISTA: PRODUCTOS (listado completo con búsqueda) -->
<!-- ============================================================ -->
<?php elseif ($seccion === 'productos'): ?>

    <!-- Mini KPIs compactos -->
    <section class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm px-4 py-3 text-center">
            <span class="block text-lg font-bold text-gray-900"><?= $totalProductos ?></span>
            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Total</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm px-4 py-3 text-center">
            <span class="block text-lg font-bold text-red-600"><?= $stockBajo ?></span>
            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Stock Bajo</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm px-4 py-3 text-center">
            <span class="block text-lg font-bold text-gray-900"><?= precio_cop($valorTotal) ?></span>
            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Valor</span>
        </div>
    </section>

    <!-- Listado completo -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <h2 class="text-base font-bold text-gray-900">Inventario de Productos</h2>
            <form class="flex flex-wrap items-center gap-2" method="get">
                <input type="hidden" name="seccion" value="productos">
                <input type="text" name="q" placeholder="Buscar producto, marca..."
                       value="<?= e($busqueda) ?>"
                       class="w-full sm:w-auto min-w-[150px] px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                <select name="cat"
                        class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= e($c) ?>" <?= $c === $categoriaFiltro ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#198754] hover:bg-[#157347] rounded-lg transition">Buscar</button>
                <?php if ($busqueda !== '' || $categoriaFiltro !== ''): ?>
                    <a href="/index.php?seccion=productos" class="px-4 py-2 text-sm font-semibold text-white bg-[#6C757D] hover:bg-[#5C636A] rounded-lg transition">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="text-left py-3 px-3">Producto</th>
                        <th class="text-left py-3 px-3 hidden md:table-cell">Categoría</th>
                        <th class="text-left py-3 px-3 hidden lg:table-cell">Marca</th>
                        <th class="text-left py-3 px-3">Precio</th>
                        <th class="text-left py-3 px-3">Stock</th>
                        <th class="text-left py-3 px-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php if (!$productos): ?>
                    <tr><td colspan="6" class="py-8 text-center text-gray-400">No se encontraron productos.</td></tr>
                <?php else: foreach ($productos as $p): ?>
                    <tr class="hover:bg-green-50/40 transition">
                        <td class="py-3 px-3 font-medium"><?= e($p['nombre']) ?></td>
                        <td class="py-3 px-3 text-gray-600 hidden md:table-cell"><?= e($p['categoria']) ?></td>
                        <td class="py-3 px-3 text-gray-600 hidden lg:table-cell"><?= e($p['marca']) ?: '—' ?></td>
                        <td class="py-3 px-3 font-mono text-sm"><?= precio_cop((float) $p['precio']) ?></td>
                        <td class="py-3 px-3">
                            <?php if ((int) $p['stock'] <= (int) $p['stock_min']): ?>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"><?= (int) $p['stock'] ?> · Bajo</span>
                            <?php else: ?>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700"><?= (int) $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <a href="/editar.php?id=<?= (int) $p['id'] ?>"
                                   class="px-3 py-1.5 text-xs font-semibold text-white bg-[#6C757D] hover:bg-[#5C636A] rounded-lg transition">Editar</a>
                                <form method="post" action="/eliminar.php"
                                      onsubmit="return confirm('¿Eliminar «<?= e($p['nombre']) ?>»?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold text-white bg-[#DC3545] hover:bg-[#BB2D3B] rounded-lg transition">Eliminar</button>
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
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5 pt-4 border-t border-gray-200">
            <span class="text-xs text-gray-500">Pág. <?= $pagina ?> de <?= $totalPaginas ?> (<?= $totalResultados ?> resultados)</span>
            <div class="flex gap-2">
                <?php if ($pagina > 1): ?>
                    <a href="?seccion=productos&p=<?= $pagina - 1 ?>&q=<?= urlencode($busqueda) ?>&cat=<?= urlencode($categoriaFiltro) ?>"
                       class="px-3 py-1.5 text-xs font-semibold text-white bg-[#6C757D] hover:bg-[#5C636A] rounded-lg transition">← Anterior</a>
                <?php endif; ?>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?seccion=productos&p=<?= $pagina + 1 ?>&q=<?= urlencode($busqueda) ?>&cat=<?= urlencode($categoriaFiltro) ?>"
                       class="px-3 py-1.5 text-xs font-semibold text-white bg-[#6C757D] hover:bg-[#5C636A] rounded-lg transition">Siguiente →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

<!-- ============================================================ -->
<!-- VISTA: REGISTRAR PRODUCTO -->
<!-- ============================================================ -->
<?php elseif ($seccion === 'registrar'): ?>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
            <h2 class="text-base font-bold text-gray-900 mb-5">Registrar Nuevo Producto</h2>
            <form action="/guardar.php" method="post">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">Nombre del producto *</label>
                        <input type="text" id="nombre" name="nombre" required maxlength="120"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>
                    <div>
                        <label for="marca" class="block text-sm font-semibold text-gray-700 mb-1">Marca</label>
                        <input type="text" id="marca" name="marca" maxlength="60"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>
                    <div>
                        <label for="categoria" class="block text-sm font-semibold text-gray-700 mb-1">Categoría</label>
                        <select id="categoria" name="categoria"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= e($c) ?>"><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="precio" class="block text-sm font-semibold text-gray-700 mb-1">Precio (COP) *</label>
                        <input type="number" id="precio" name="precio" min="0" step="any" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>
                    <div>
                        <label for="stock" class="block text-sm font-semibold text-gray-700 mb-1">Stock (unidades) *</label>
                        <input type="number" id="stock" name="stock" min="0" step="1" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>
                    <div>
                        <label for="stock_min" class="block text-sm font-semibold text-gray-700 mb-1">Stock mínimo (alerta)</label>
                        <input type="number" id="stock_min" name="stock_min" min="0" step="1" value="5"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>
                </div>

                <div class="mt-4">
                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" maxlength="500"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none resize-y min-h-[72px]"></textarea>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-[#198754] hover:bg-[#157347] rounded-lg shadow-sm transition">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

<?php endif; ?>

<?php
obtener_footer();
