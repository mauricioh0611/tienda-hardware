<?php
require __DIR__ . '/../src/db.php';

sesion_iniciar();

// Si ya está autenticado, redirigir al inicio
if (isset($_SESSION['usuario_id'])) {
    header('Location: /index.php');
    exit;
}

$msg  = $_GET['msg'] ?? '';
$tipo = $_GET['tipo'] ?? 'ok';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errores = [];
    if ($email === '')    $errores[] = 'El correo es obligatorio.';
    if ($password === '') $errores[] = 'La contraseña es obligatoria.';

    if (!$errores) {
        $usuario = verificar_credenciales($email, $password);
        if ($usuario) {
            $_SESSION['usuario_id']    = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email']  = $usuario['email'];
            header('Location: /index.php?msg=' . urlencode('Bienvenido de nuevo, ' . $usuario['nombre'] . '.') . '&tipo=ok');
            exit;
        } else {
            $errores[] = 'Correo o contraseña incorrectos.';
        }
    }

    $msg  = implode(' ', $errores);
    $tipo = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Tienda de Hardware</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<header class="navbar">
    <span class="logo"></span>
    <h1>Sistema de Gestión - Tienda de Hardware</h1>
</header>

<main class="contenedor">
    <section class="tarjeta tarjeta-auth">
        <h2>Iniciar Sesión</h2>

        <?php if ($msg): ?>
            <div class="alerta alerta-<?= e($tipo) ?>"><?= e($msg) ?></div>
        <?php endif; ?>

        <form action="/login.php" method="post">
            <div>
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" required maxlength="120"
                       value="<?= e($_POST['email'] ?? '') ?>" placeholder="ejemplo@correo.com">
            </div>

            <div>
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required minlength="6"
                       placeholder="Mínimo 6 caracteres">
            </div>

            <div class="mt">
                <button type="submit" class="btn btn-verde btn-block">Iniciar Sesión</button>
            </div>
        </form>

        <p class="auth-link">
            ¿No tienes cuenta? <a href="/registro.php">Regístrate aquí</a>
        </p>
    </section>
</main>

<footer>
    Trabajo Colaborativo - Grupo 5 · Politécnico Grancolombiano · 2026
</footer>

</body>
</html>
