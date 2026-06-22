<?php
/**
 * Conexión a la base de datos SQLite y creación automática del esquema.
 * Devuelve una única instancia PDO (patrón singleton sencillo).
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // La base de datos vive FUERA del directorio público (seguridad).
        $dbFile = __DIR__ . '/../database/tienda.db';

        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        // Migración: crea las tablas si no existen.
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS categorias (
                id     INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL UNIQUE
            );
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS productos (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre      TEXT    NOT NULL,
                categoria   TEXT    NOT NULL DEFAULT 'General',
                marca       TEXT    NOT NULL DEFAULT '',
                precio      REAL    NOT NULL DEFAULT 0,
                stock       INTEGER NOT NULL DEFAULT 0,
                stock_min   INTEGER NOT NULL DEFAULT 5,
                descripcion TEXT    NOT NULL DEFAULT '',
                creado_en   TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
            );
        ");

        // Tabla de usuarios para autenticación
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre     TEXT    NOT NULL,
                email      TEXT    NOT NULL UNIQUE,
                password   TEXT    NOT NULL,
                creado_en  TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
            );
        ");

        // Usuario administrador por defecto (solo la primera vez)
        $hayUsuarios = (int) $pdo->query('SELECT COUNT(*) AS c FROM usuarios')->fetch()['c'];
        if ($hayUsuarios === 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
            $stmt->execute(['Administrador', 'admin@tienda.com', $hash]);
        }

        // Datos de ejemplo solo la primera vez (tabla vacía).
        $hay = (int) $pdo->query('SELECT COUNT(*) AS c FROM productos')->fetch()['c'];
        if ($hay === 0) {
            $semilla = [
                ['Procesador Ryzen 5 5600', 'Procesadores', 'AMD', 489900, 12, 4, 'CPU 6 núcleos / 12 hilos, socket AM4.'],
                ['Tarjeta Gráfica RTX 4060', 'Tarjetas Gráficas', 'NVIDIA', 1599900, 6, 3, 'GPU 8GB GDDR6 para gaming 1080p/1440p.'],
                ['Memoria RAM 16GB DDR4 3200', 'Memorias RAM', 'Kingston', 219900, 20, 6, 'Módulo de 16GB, frecuencia 3200 MHz.'],
                ['SSD NVMe 1TB', 'Almacenamiento', 'Western Digital', 329900, 3, 5, 'Unidad de estado sólido M.2 PCIe Gen3.'],
                ['Fuente de Poder 650W 80+ Bronze', 'Fuentes de Poder', 'Corsair', 299900, 9, 4, 'PSU certificada 80 Plus Bronze.'],
            ];
            $st = $pdo->prepare(
                'INSERT INTO productos (nombre, categoria, marca, precio, stock, stock_min, descripcion)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($semilla as $p) {
                $st->execute($p);
            }
        }
    }

    return $pdo;
}

/** Escapa texto para mostrar en HTML de forma segura. */
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Formatea un precio en pesos colombianos. */
function precio_cop(float $valor): string
{
    return '$' . number_format($valor, 0, ',', '.');
}

/**
 * Inicia la sesión si no está iniciada.
 */
function sesion_iniciar(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica si el usuario está autenticado.
 * Redirige al login si no lo está.
 */
function requiere_autenticacion(): void
{
    sesion_iniciar();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login.php?msg=' . urlencode('Debes iniciar sesión primero.') . '&tipo=error');
        exit;
    }
}

/**
 * Verifica credenciales contra la BD.
 * Devuelve los datos del usuario si son correctas, o false en caso contrario.
 */
function verificar_credenciales(string $email, string $password): array|false
{
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        return $usuario;
    }
    return false;
}

/**
 * Registra un nuevo usuario.
 * Devuelve true si se registró correctamente, o un string con el error.
 */
function registrar_usuario(string $nombre, string $email, string $password): true|string
{
    $pdo = db();

    // Validar que el email no exista
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    if ((int) $stmt->fetch()['c'] > 0) {
        return 'El correo electrónico ya está registrado.';
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$nombre, $email, $hash]);
    return true;
}
