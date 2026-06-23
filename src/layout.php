<?php
/**
 * Layout reutilizable con sidebar lateral y navbar
 * Uso: obtener_header('Título', 'pagina-activa'); ... HTML ... obtener_footer();
 */

function obtener_header(string $titulo, string $seccion = 'dashboard'): void {
    $flash = flash_recuperar();
    $msg   = $flash['msg'];
    $tipo  = $flash['tipo'];

    // Mapa de secciones activas
    $activa = fn(string $s): string => $seccion === $s ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> — Tienda de Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand:  { DEFAULT: '#2E9E2E', dark: '#198754', darker: '#157347' },
                        danger: { DEFAULT: '#DC3545', dark: '#BB2D3B' },
                        muted:  { DEFAULT: '#6C757D', dark: '#5C636A' },
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="bg-[#F0F2F5] text-[#212529] font-sans antialiased min-h-screen flex">

    <!-- ==================== SIDEBAR ==================== -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-[#2E9E2E] text-white flex flex-col shadow-xl transition-transform duration-300 -translate-x-full lg:translate-x-0 lg:static lg:inset-auto">

        <!-- Logo / Brand -->
        <div class="h-14 flex items-center gap-3 px-5 border-b border-white/15 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-200 to-green-800 flex items-center justify-center text-xs font-bold text-white shadow-inner">TH</div>
            <div>
                <span class="block text-sm font-bold leading-tight">Tienda Hardware</span>
                <span class="block text-[10px] text-white/60 font-medium tracking-wide">Sistema de Gestión</span>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all <?= $activa('dashboard') ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="/index.php?seccion=productos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all <?= $activa('productos') ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Productos
            </a>
            <a href="/index.php?seccion=registrar" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all <?= $activa('registrar') ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                Nuevo Producto
            </a>
        </nav>

        <!-- Usuario / Cerrar Sesión -->
        <div class="px-3 py-4 border-t border-white/15 flex-shrink-0 space-y-2">
            <div class="flex items-center gap-3 px-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold"><?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?></div>
                <div class="min-w-0">
                    <span class="block text-sm font-medium truncate"><?= e($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
                    <span class="block text-[10px] text-white/50 truncate"><?= e($_SESSION['usuario_email'] ?? '') ?></span>
                </div>
            </div>
            <a href="/logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/70 hover:bg-white/10 hover:text-white transition-all">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Overlay para móvil -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- ==================== CONTENIDO PRINCIPAL ==================== -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar (móvil: hamburguesa + título) -->
        <header class="lg:hidden bg-white border-b border-gray-200 h-14 flex items-center gap-3 px-4 shadow-sm flex-shrink-0">
            <button onclick="toggleSidebar()" class="p-2 -ml-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <span class="text-sm font-semibold text-gray-800 truncate"><?= e($titulo) ?></span>
            <div class="ml-auto w-8 h-8 rounded-full bg-[#2E9E2E] flex items-center justify-center text-white text-xs font-bold"><?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?></div>
        </header>

        <!-- Flash message (global) -->
        <?php if ($msg): ?>
        <div class="animate-slide-down mx-4 mt-4 mb-0 px-4 py-3 rounded-lg border text-sm font-medium
            <?= $tipo === 'ok'
                ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                : 'bg-red-50 border-red-200 text-red-800' ?>">
            <?= e($msg) ?>
        </div>
        <?php endif; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
<?php
}

function obtener_footer(): void {
?>
        </main>

        <!-- Footer -->
        <footer class="text-center text-xs text-gray-400 py-4 border-t border-gray-200 bg-white flex-shrink-0">
            Trabajo Colaborativo - Grupo 5 · Politécnico Grancolombiano · 2026
        </footer>
    </div>

    <!-- Script: toggle sidebar -->
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }
    </script>

</body>
</html>
<?php
}
