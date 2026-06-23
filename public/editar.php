<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/layout.php';

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

obtener_header('Editar Producto', 'productos');
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-bold text-gray-900">Editar Producto</h2>
            <span class="text-xs text-gray-400 font-mono">#<?= (int) $p['id'] ?></span>
        </div>

        <form action="/guardar.php" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1">
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">Nombre del producto *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="120" value="<?= e($p['nombre']) ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                </div>
                <div>
                    <label for="marca" class="block text-sm font-semibold text-gray-700 mb-1">Marca</label>
                    <input type="text" id="marca" name="marca" maxlength="60" value="<?= e($p['marca']) ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                </div>
                <div>
                    <label for="categoria" class="block text-sm font-semibold text-gray-700 mb-1">Categoría</label>
                    <select id="categoria" name="categoria"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= e($c) ?>" <?= $c === $p['categoria'] ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="precio" class="block text-sm font-semibold text-gray-700 mb-1">Precio (COP) *</label>
                    <input type="number" id="precio" name="precio" min="0" step="any" required value="<?= e((string) $p['precio']) ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                </div>
                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-1">Stock (unidades) *</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" required value="<?= (int) $p['stock'] ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                </div>
                <div>
                    <label for="stock_min" class="block text-sm font-semibold text-gray-700 mb-1">Stock mínimo (alerta)</label>
                    <input type="number" id="stock_min" name="stock_min" min="0" step="1" value="<?= (int) $p['stock_min'] ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                </div>
            </div>

            <div class="mt-4">
                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
                <textarea id="descripcion" name="descripcion" maxlength="500"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none resize-y min-h-[72px]"><?= e($p['descripcion']) ?></textarea>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-[#198754] hover:bg-[#157347] rounded-lg shadow-sm transition">Actualizar Producto</button>
                <a href="/index.php?seccion=productos"
                   class="px-5 py-2.5 text-sm font-semibold text-white bg-[#6C757D] hover:bg-[#5C636A] rounded-lg transition">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
obtener_footer();
