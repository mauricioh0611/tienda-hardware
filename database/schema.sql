-- Esquema de la base de datos SQLite (referencia).
-- La aplicación crea estas tablas automáticamente en src/db.php.

CREATE TABLE IF NOT EXISTS categorias (
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);

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
