<div align="center">

# 🖥️ Sistema de Gestión - Tienda de Hardware

**Aplicación web para la gestión y venta de componentes de hardware**

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://sqlite.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/Licencia-Academic%20Free-2E9E2E)](LICENSE)

</div>

---

## 📋 Tabla de Contenidos

- [Descripción del Proyecto](#-descripción-del-proyecto)
- [Capturas de Pantalla](#-capturas-de-pantalla)
- [Stack Tecnológico](#-stack-tecnológico)
- [Funcionalidades](#-funcionalidades)
- [Dashboard](#-dashboard)
- [Seguridad](#-seguridad)
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
- Dashboard con **KPIs** en tiempo real (total productos, stock bajo, valor inventario, categorías)
- **Sidebar** lateral responsivo con navegación entre Dashboard, Productos y Nuevo Producto
- Registro y administración de productos (procesadores, tarjetas gráficas, memorias RAM, almacenamiento, fuentes de poder, periféricos, etc.)
- Alerta visual de **stock bajo** para control de inventario
- **Búsqueda** por nombre, marca y descripción + **filtro** por categoría
- **Paginación** automática (10 productos por página)
- **Gráfico de barras** de productos por categoría
- Interfaz moderna con **Tailwind CSS** y tema visual verde
- Sistema de **autenticación** con registro de usuarios y sesiones seguras
- **Protección CSRF** en todos los formularios
- **Auditoría** de acciones (crear, editar, eliminar productos)
- Sistema de **flash messages** desde sesión

---

## 🖼️ Capturas de Pantalla

| Dashboard | Listado de Productos |
|:---------:|:--------------------:|
| KPIs, gráfico por categoría, stock bajo y productos recientes | Búsqueda, filtro, paginación y acciones CRUD |

| Nuevo Producto | Editar Producto |
|:--------------:|:---------------:|
| Formulario en grid de 2 columnas con validación | Formulario precargado con datos existentes |

| Login | Registro |
|:-----:|:--------:|
| Card centrada con hint de credenciales de prueba | Card centrada con confirmación de contraseña |

---

## 🛠️ Stack Tecnológico

<div align="center">

| Capa | Tecnología | Versión |
|:----:|:----------:|:-------:|
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white) | 8.1+ |
| **Base de Datos** | ![SQLite](https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white) | 3.x |
| **Frontend** | ![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white) | 3.x (CDN) |
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
| RF-04 | **Eliminar producto** | Eliminación con confirmación previa y protección CSRF |
| RF-05 | **Validar datos** | Nombre obligatorio; precio y stock no negativos |
| RF-06 | **Alerta stock bajo** | Badge rojo `rounded-full` cuando `stock ≤ stock_min` |
| RF-07 | **Clasificar por categoría** | 9 categorías predefinidas |
| RF-08 | **Mensajes de retroalimentación** | Flash messages animados desde sesión |
| RF-09 | **Dashboard con KPIs** | Métricas en tiempo real con íconos |
| RF-10 | **Búsqueda y filtros** | Búsqueda por nombre/marca/descripción + filtro por categoría |

### No Funcionales (RNF)

| ID | Funcionalidad |
|:--:|:-------------:|
| RNF-01 | Interfaz responsiva con **sidebar colapsable** y tema visual verde |
| RNF-02 | Consultas preparadas (PDO) contra inyección SQL y escape HTML (XSS) |
| RNF-03 | SQLite embebido — sin servidor de BD externo |
| RNF-04 | Código organizado por capas (`src/layout.php` + `src/db.php`) |
| RNF-05 | Respuestas < 1 segundo para catálogos de miles de productos |
| RNF-06 | Servicio como demonio (Nginx + PHP-FPM) o Docker |
| RNF-07 | Compatible con Chrome, Firefox y Edge |
| RNF-08 | Trazabilidad con fecha de creación en cada producto |
| RNF-09 | **Protección CSRF** con tokens por sesión |
| RNF-10 | **Auditoría** de acciones (crear, editar, eliminar) |

---

## 📊 Dashboard

El dashboard ofrece una vista ejecutiva del inventario con:

### KPIs (4 indicadores)
| Indicador | Descripción |
|:---------:|:-----------:|
| 📦 **Productos** | Total de productos registrados |
| ⚠️ **Stock Bajo** | Productos con stock ≤ stock mínimo |
| 💰 **Valor Inventario** | Suma total (precio × stock) formateado en COP |
| 🗂️ **Categorías** | Cantidad de categorías distintas |

### Componentes del Dashboard
1. **Gráfico de barras** — Distribución de productos por categoría con barras proporcionales animadas
2. **Alertas de Stock Bajo** — Lista de productos críticos con cantidad actual y stock mínimo
3. **Productos Recientes** — Tabla con los últimos 5 productos registrados

---

## 🔐 Seguridad

| Medida | Implementación |
|:------:|:--------------:|
| **CSRF** | Tokens únicos por sesión (`random_bytes(32)` + `hash_equals()`) en todos los formularios POST |
| **XSS** | Escape de salida HTML con `htmlspecialchars($val, ENT_QUOTES)` |
| **SQL Injection** | Consultas preparadas con PDO (bound parameters) |
| **Session Fixation** | `session_regenerate_id(true)` en login y registro |
| **Contraseñas** | Hash bcrypt con `password_hash()` + `password_verify()` |
| **Flash Messages** | Mensajes vía sesión (`$_SESSION['_flash']`), no por URL |
| **Auditoría** | Tabla `auditoria` registra cada acción con usuario, producto y detalle |
| **BD no accesible** | Base de datos fuera de `public/` |

---

## 📁 Estructura del Proyecto

```
tienda-hardware/
├── public/                       # Raíz pública (document root)
│   ├── index.php                 # Dashboard + listado + formulario (3 vistas)
│   ├── guardar.php               # Crear o actualizar producto (POST)
│   ├── editar.php                # Formulario de edición precargado
│   ├── eliminar.php              # Eliminar producto (POST + CSRF)
│   ├── login.php                 # Inicio de sesión
│   ├── registro.php              # Registro de usuarios
│   ├── logout.php                # Cerrar sesión
│   └── assets/
│       └── style.css             # Scrollbar personalizado + animaciones
├── src/
│   ├── db.php                    # Conexión PDO + migración + helpers
│   └── layout.php                # Layout con sidebar + navbar + footer
├── docker/
│   ├── nginx.conf                # Configuración Nginx para contenedor
├── database/
│   ├── schema.sql                # Esquema SQL de referencia
│   └── tienda.db                 # Base de datos (NO se versiona)
├── Dockerfile                    # Imagen PHP 8.1 Alpine + SQLite
├── docker-compose.yml            # Orquestación Nginx + PHP
├── .dockerignore                 # Exclusiones para Docker
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

> 🎉 La base de datos `database/tienda.db` se crea automáticamente la primera vez, junto con 5 productos de ejemplo y un usuario administrador.

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
- Protección CSRF mediante token por sesión
- Regeneración de ID de sesión (`session_regenerate_id`) en login/registro

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
