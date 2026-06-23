<div align="center">

# 🖥️ Sistema de Gestión - Tienda de Hardware

**Aplicación web para la gestión y venta de componentes de hardware**

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://sqlite.org)
[![License](https://img.shields.io/badge/Licencia-Academic%20Free-2E9E2E)](LICENSE)

</div>

---

## 📋 Tabla de Contenidos

- [Descripción del Proyecto](#-descripción-del-proyecto)
- [Requerimientos del Sistema](#-requerimientos-del-sistema)
- [Stack Tecnológico](#-stack-tecnológico)
- [Funcionalidades](#-funcionalidades)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Instalación y Ejecución Local](#-instalación-y-ejecución-local)
- [Autenticación](#-autenticación)
- [Usuarios de Prueba](#-usuarios-de-prueba)
- [Despliegue en Producción](#-despliegue-en-producción)
- [Información Académica](#-información-académica)
- [Licencia](#-licencia)

---

## 📖 Descripción del Proyecto

Sistema web desarrollado en **PHP 8 + SQLite** que permite gestionar el catálogo de productos y el inventario de una tienda de componentes de hardware. El sistema reemplaza los procesos manuales basados en hojas de cálculo, centralizando la información de los productos y permitiendo operaciones **CRUD** (Crear, Leer, Actualizar, Eliminar) de forma ágil y segura.

### Principales características:
- Registro y administración de productos (procesadores, tarjetas gráficas, memorias RAM, almacenamiento, fuentes de poder, periféricos, etc.)
- Alerta visual de **stock bajo** para control de inventario
- Interfaz responsiva con tema visual verde
- Sistema de **autenticación** con registro de usuarios y sesiones seguras

---

## ⚙️ Requerimientos del Sistema

| Componente | Especificación |
|------------|---------------|
| **PHP** | 8.1 o superior |
| **Servidor web** | Nginx 1.18+ (producción) / PHP built-in (desarrollo) |
| **Base de datos** | SQLite 3 (vía PDO) |
| **PHP-FPM** | 8.1+ (producción) |
| **Navegadores** | Chrome, Firefox, Edge (versiones modernas) |

---

## 🛠️ Stack Tecnológico

<div align="center">

| Capa | Tecnología | Versión |
|:----:|:----------:|:-------:|
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white) | 8.1+ |
| **Base de Datos** | ![SQLite](https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white) | 3.x |
| **Frontend** | ![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white) ![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white) | Estándar |
| **Servidor** | ![Nginx](https://img.shields.io/badge/Nginx-009639?logo=nginx&logoColor=white) | 1.18+ |
| **Infraestructura** | ![AWS](https://img.shields.io/badge/AWS%20Lightsail-FF9900?logo=amazonaws&logoColor=white) | Ubuntu 22.04 / 24.04 |


</div>

---

## ✅ Funcionalidades

### Funcionales (RF)

| ID | Funcionalidad | Descripción |
|:--:|:-------------:|-------------|
| RF-01 | **Registrar producto** | Formulario completo con validación de datos |
| RF-02 | **Listar productos** | Tabla con nombre, categoría, marca, precio, stock y acciones |
| RF-03 | **Editar producto** | Modificación de todos los campos de un producto existente |
| RF-04 | **Eliminar producto** | Eliminación con confirmación previa |
| RF-05 | **Validar datos** | Nombre obligatorio; precio y stock no negativos |
| RF-06 | **Alerta stock bajo** | Resaltado visual cuando `stock ≤ stock_min` |
| RF-07 | **Clasificar por categoría** | 9 categorías predefinidas |
| RF-08 | **Mensajes de retroalimentación** | Alertas de éxito/error tras cada operación |

### No Funcionales (RNF)

| ID | Funcionalidad |
|:--:|:-------------:|
| RNF-01 | Interfaz responsiva con tema visual verde |
| RNF-02 | Consultas preparadas (PDO) contra inyección SQL y escape HTML (XSS) |
| RNF-03 | SQLite embebido — sin servidor de BD externo |
| RNF-04 | Código organizado por capas y comentado |
| RNF-05 | Respuestas < 1 segundo para catálogos de miles de productos |
| RNF-06 | Servicio como demonio (Nginx + PHP-FPM) |
| RNF-07 | Compatible con Chrome, Firefox y Edge |
| RNF-08 | Trazabilidad con fecha de creación en cada producto |

---

## 📁 Estructura del Proyecto

```
tienda-hardware/
├── public/                       # Raíz pública (document root)
│   ├── index.php                 # Listado de productos + formulario de registro
│   ├── guardar.php               # Crear o actualizar producto (POST)
│   ├── editar.php                # Formulario de edición precargado
│   ├── eliminar.php              # Eliminar producto
│   ├── login.php                 # Inicio de sesión
│   ├── registro.php              # Registro de usuarios
│   ├── logout.php                # Cerrar sesión
│   └── assets/
│       └── style.css             # Tema visual verde
├── src/
│   └── db.php                    # Conexión PDO + migración + helpers de autenticación
├── database/
│   ├── schema.sql                # Esquema SQL de referencia
│   └── tienda.db                 # Base de datos (se genera sola — NO se versiona)
├── REQUERIMIENTOS.md             # Documento completo de requerimientos
├── README.md                     # Este archivo
└── .gitignore                    # Archivos ignorados por Git
```

> 🔒 La base de datos vive **fuera** de `public/` por seguridad: Nginx nunca la sirve directamente.

---

## 🚀 Instalación y Ejecución Local

### Requisitos previos

- PHP 8.1 o superior instalado
- Extensión `pdo_sqlite` habilitada

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/TU_USUARIO/tienda-hardware.git
cd tienda-hardware

# 2. Iniciar el servidor embebido de PHP
php -S 127.0.0.1:8000 -t public
```

### 3. Abrir en el navegador

```
http://127.0.0.1:8000
```

> 🎉 La base de datos `database/tienda.db` se crea automáticamente la primera vez, junto con 5 productos de ejemplo.

---

## 🔐 Autenticación

El sistema cuenta con un módulo de autenticación que protege el acceso al inventario.

| Ruta | Descripción |
|:----:|:-----------:|
| [`/login.php`](public/login.php) | Inicio de sesión |
| [`/registro.php`](public/registro.php) | Crear cuenta nueva |
| [`/logout.php`](public/logout.php) | Cerrar sesión |

**Características de seguridad:**
- Contraseñas almacenadas con **bcrypt** (`password_hash()` + `password_verify()`)
- Sesiones PHP con destrucción segura al cerrar sesión
- Validación de email único en el registro
- Protección CSRF mediante verificación de sesión

---

## 👤 Usuarios de Prueba

El sistema crea automáticamente un **usuario administrador** la primera vez que se ejecuta:

| Correo | Contraseña | Rol |
|:------:|:----------:|:---:|
| `admin@tienda.com` | `admin123` | Administrador |

> También puedes crear nuevos usuarios desde el formulario de registro en [`/registro.php`](public/registro.php).

---

## ☁️ Despliegue en Producción

### Opción 1 — Manual (Nginx + PHP-FPM)

Consulta la **[guía completa de despliegue manual](REQUERIMIENTOS.md#10-despliegue-paso-a-paso-en-aws-lightsail-nginx--php)** en el documento de requerimientos para implementar en AWS Lightsail con:

1. Creación de instancia Lightsail (Ubuntu 22.04 / 24.04)
2. Instalación de Nginx + PHP-FPM + SQLite
3. Configuración del sitio con `root` apuntando a `public/`
4. Asignación de permisos para `www-data`
5. (Opcional) HTTPS con Certbot

### Opción 2 — Docker (recomendado)

También puedes desplegar con **Docker Compose**, más rápido y sin configuraciones manuales. Consulta la **[guía de despliegue Docker](REQUERIMIENTOS.md#11-despliegue-con-docker-en-aws-lightsail)** en el documento de requerimientos.

Archivos Docker incluidos:

| Archivo | Descripción |
|---------|-------------|
| [`Dockerfile`](Dockerfile) | Imagen PHP 8.1 Alpine + SQLite |
| [`docker-compose.yml`](docker-compose.yml) | Orquestación Nginx + PHP |
| [`docker/nginx.conf`](docker/nginx.conf) | Configuración de Nginx para el contenedor |
| [`.dockerignore`](.dockerignore) | Exclusiones para la imagen Docker |

Comandos rápidos:

```bash
# Construir y ejecutar
docker-compose up -d --build

# Ver logs
docker-compose logs -f

# Detener
docker-compose down
```
---

## 🎓 Información Académica

| Dato | Información |
|:----:|:-----------:|
| **Institución** | Politécnico Grancolombiano |
| **Facultad** | Ingeniería y Ciencias Básicas |
| **Materia** | Módulo de Integración Continua |
| **Profesor** | Jesús Figueroa Guerrero |
| **Período** | 2026 |
| **Grupo** | Grupo 5 |

> 📌 Desarrollado para fines educativos.

### 👥 Integrantes del Grupo

| Nombre | Rol |
|:------:|:---:|
| Nelson Enriquez Aguilar | Desarrollador |
| Alvaro Holguin Vega | Desarrollador |
| Mauricio Hurtado | Desarrollador |
| Nestor Antonio Romero Guerrero | Desarrollador |

---

## 📄 Licencia

Este proyecto se distribuye con fines académicos como parte del trabajo colaborativo del **Politécnico Grancolombiano — Módulo de Integración Continua**.

---

<div align="center">

**Desarrollado con ❤️ por el Grupo 5 · Politécnico Grancolombiano · 2026**

[⬆ Volver al inicio](#-sistema-de-gestión---tienda-de-hardware)

</div>
