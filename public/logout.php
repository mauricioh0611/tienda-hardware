<?php
require __DIR__ . '/../src/db.php';

sesion_iniciar();

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión si existe
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destruir la sesión
session_destroy();

header('Location: /login.php?msg=' . urlencode('Has cerrado sesión correctamente.') . '&tipo=ok');
exit;
