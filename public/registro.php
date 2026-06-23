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
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<header class="navbar">
    <span class="logo"></span>
    <h1>Sistema de Gestión - Tienda de Hardware</h1>
</header>

<main class="contenedor">
    <section class="tarjeta tarjeta-auth">
        <h2>Crear Cuenta</h2>

        <?php if ($msg): ?>
            <div class="alerta alerta-<?= e($tipo) ?>"><?= e($msg) ?></div>
        <?php endif; ?>

        <form action="/registro.php" method="post">
            <?= csrf_field() ?>

            <div>
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" required maxlength="100"
                       placeholder="Tu nombre">
            </div>

            <div>
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" required maxlength="120"
                       placeholder="ejemplo@correo.com">
            </div>

            <div class="fila">
                <div>
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required minlength="6"
                           placeholder="Mínimo 6 caracteres">
                </div>
                <div>
                    <label for="confirmar">Confirmar contraseña *</label>
                    <input type="password" id="confirmar" name="confirmar" required minlength="6"
                           placeholder="Repite la contraseña">
                </div>
            </div>

            <div class="mt">
                <button type="submit" class="btn btn-verde btn-block">Crear Cuenta</button>
            </div>
        </form>

        <p class="auth-link">
            ¿Ya tienes cuenta? <a href="/login.php">Inicia sesión aquí</a>
        </p>
    </section>
</main>

<footer>
    Trabajo Colaborativo - Grupo 5 · Politécnico Grancolombiano · 2026
</footer>

</body>
</html>
