<?php
require __DIR__ . '/../src/db.php';

sesion_iniciar();

// Si ya está autenticado, redirigir al inicio
if (isset($_SESSION['usuario_id'])) {
    header('Location: /index.php');
    exit;
}

$flash = flash_recuperar();
$msg   = $flash['msg'];
$tipo  = $flash['tipo'];

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();

    $nombre    = trim($_POST['nombre'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    $errores = [];
    if ($nombre === '')        $errores[] = 'El nombre es obligatorio.';
    if ($email === '')         $errores[] = 'El correo electrónico es obligatorio.';
    if ($password === '')      $errores[] = 'La contraseña es obligatoria.';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $confirmar) $errores[] = 'Las contraseñas no coinciden.';

    if (!$errores) {
        $resultado = registrar_usuario($nombre, $email, $password);
        if ($resultado === true) {
            // Iniciar sesión automáticamente
            $usuario = verificar_credenciales($email, $password);
            if ($usuario) {
                session_regenerate_id(true);
                $_SESSION['_csrf'] = bin2hex(random_bytes(32));
                $_SESSION['usuario_id']    = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email']  = $usuario['email'];
            }
            flash('Cuenta creada correctamente. ¡Bienvenido!', 'ok');
            header('Location: /index.php');
            exit;
        } else {
            $errores[] = $resultado;
        }
    }

    flash(implode(' ', $errores), 'error');
    header('Location: /registro.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Tienda de Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:  { DEFAULT: '#2E9E2E', dark: '#198754', darker: '#157347' },
                        danger:   { DEFAULT: '#DC3545', dark: '#BB2D3B' },
                        muted:    { DEFAULT: '#6C757D', dark: '#5C636A' },
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="bg-[#F8F9FA] text-[#212529] font-sans antialiased min-h-screen flex flex-col">

    <!-- ==================== NAVBAR ==================== -->
    <header class="bg-[#2E9E2E] text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center gap-3">
            <div class="w-[18px] h-[18px] rounded-full bg-gradient-to-br from-green-200 to-green-700 flex-shrink-0"></div>
            <h1 class="text-base sm:text-lg font-semibold truncate">Sistema de Gestión - Tienda de Hardware</h1>
        </div>
    </header>

    <!-- ==================== MAIN ==================== -->
    <main class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">

            <!-- Flash message -->
            <?php if ($msg): ?>
            <div class="animate-slide-down mb-5 px-4 py-3 rounded-lg border text-sm font-medium
                <?= $tipo === 'ok'
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                    : 'bg-red-50 border-red-200 text-red-800' ?>">
                <?= e($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Card -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 sm:p-8">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-200 to-green-700 mx-auto mb-3"></div>
                    <h2 class="text-xl font-bold text-gray-900">Crear Cuenta</h2>
                    <p class="text-sm text-gray-500 mt-1">Regístrate para gestionar el inventario</p>
                </div>

                <form action="/registro.php" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" required maxlength="100"
                               placeholder="Tu nombre"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo electrónico</label>
                        <input type="email" id="email" name="email" required maxlength="120"
                               placeholder="ejemplo@correo.com"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-0">
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
                            <input type="password" id="password" name="password" required minlength="6"
                                   placeholder="Mín. 6 caracteres"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                        </div>
                        <div class="mb-5">
                            <label for="confirmar" class="block text-sm font-semibold text-gray-700 mb-1">Confirmar contraseña</label>
                            <input type="password" id="confirmar" name="confirmar" required minlength="6"
                                   placeholder="Repite la contraseña"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#198754]/30 focus:border-[#198754] outline-none">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 text-sm font-semibold text-white bg-[#198754] hover:bg-[#157347] rounded-lg shadow-sm transition">Crear Cuenta</button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-5">
                    ¿Ya tienes cuenta?
                    <a href="/login.php" class="font-semibold text-[#198754] hover:text-[#157347] transition">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
    </main>

    <!-- ==================== FOOTER ==================== -->
    <footer class="text-center text-xs text-gray-500 py-5 border-t border-gray-200 bg-white">
        Trabajo Colaborativo - Grupo 5 · Politécnico Grancolombiano · 2026
    </footer>

</body>
</html>
