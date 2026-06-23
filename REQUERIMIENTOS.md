# Requerimientos del Sistema Web para la Gestión de una Tienda de Hardware

**Proyecto:** Sistema Web para la Gestión y Venta de Componentes de Hardware
**Grupo:** Grupo 5 · Politécnico Grancolombiano · 2026
**Stack:** PHP 8 + SQLite + Nginx · Despliegue en AWS Lightsail
**Propósito de este documento:** servir como guía técnica para implementar el sistema y desplegarlo en un servidor **Lightsail**.

---

## 1. Descripción general

El sistema es una aplicación web que permite gestionar el catálogo de productos y el inventario de una tienda de componentes de hardware (procesadores, tarjetas gráficas, memorias RAM, almacenamiento, fuentes de poder, periféricos, etc.).

El objetivo es centralizar la información de los productos y permitir su registro, consulta, actualización y eliminación (operaciones CRUD), reemplazando los procesos manuales basados en hojas de cálculo. El sistema incluye una alerta visual de **stock bajo** para apoyar el control de inventario.

> El enfoque del sistema está en el **producto** (gestionar productos e inventario), no en las herramientas. Git, GitHub, Docker o Lightsail son medios para construir y desplegar la solución, no su finalidad.

---

## 2. Stack tecnológico

| Componente | Tecnología | Versión sugerida |
|------------|------------|------------------|
| Lenguaje backend | PHP | 8.1+ |
| Base de datos | SQLite (vía PDO) | 3 |
| Servidor web | Nginx | 1.18+ |
| Procesador PHP | PHP-FPM | 8.1+ |
| Frontend | HTML5 + Tailwind CSS 3 (CDN) | — |
| Infraestructura | AWS Lightsail (Ubuntu 22.04 / 24.04) | — |
| Control de versiones | Git + GitHub | — |

No se usa framework: PHP plano con PDO es suficiente para un MVP y facilita el despliegue.

---

## 3. Paleta de colores (tomada de las interfaces del documento)

El tema visual reutiliza los colores verdes de las capturas entregadas.

| Uso | Color | Hex |
|-----|-------|-----|
| Encabezado / barra superior | Verde principal | `#2E9E2E` |
| Botones primarios (Guardar / Actualizar) | Verde oscuro | `#198754` |
| Hover de botones verdes | Verde hover | `#157347` |
| Botón eliminar / peligro | Rojo | `#DC3545` |
| Botón secundario (Editar / Cancelar) | Gris | `#6C757D` |
| Fondo general | Gris muy claro | `#F8F9FA` |
| Texto | Casi negro | `#212529` |
| Borde de tarjetas / inputs | Gris borde | `#DEE2E6` |

Las variables CSS ya están definidas en `public/assets/style.css` con estos nombres.

---

## 4. Estructura del proyecto

```
tienda-hardware/
├── public/                  # Raíz pública servida por Nginx (document root)
│   ├── index.php            # Dashboard + listado + formulario (3 vistas vía ?seccion=)
│   ├── guardar.php          # Crea o actualiza un producto (POST + CSRF)
│   ├── editar.php           # Formulario de edición con sidebar layout
│   ├── eliminar.php         # Elimina un producto (POST + CSRF + auditoría)
│   ├── login.php            # Inicio de sesión
│   ├── registro.php         # Registro de usuarios
│   ├── logout.php           # Cerrar sesión
│   └── assets/
│       └── style.css        # Scrollbar personalizado + animaciones
├── src/
│   ├── db.php               # Conexión PDO + migración + helpers de auth y CSRF
│   └── layout.php           # Layout con sidebar, navbar y footer
├── docker/
│   └── nginx.conf           # Configuración Nginx para Docker
├── database/
│   ├── schema.sql           # Esquema SQL de referencia
│   └── tienda.db            # Base de datos (se genera sola; NO se versiona)
├── Dockerfile               # Imagen PHP 8.1 Alpine + SQLite
├── docker-compose.yml       # Orquestación Nginx + PHP
├── .dockerignore
├── .gitignore
├── REQUERIMIENTOS.md
└── README.md
```

La base de datos vive **fuera** de `public/` por seguridad: así Nginx nunca la sirve directamente.

---

## 5. Modelo de datos

Tabla principal `productos`:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INTEGER PK AUTOINCREMENT | Identificador único |
| `nombre` | TEXT NOT NULL | Nombre del producto |
| `categoria` | TEXT | Categoría (Procesadores, Tarjetas Gráficas, etc.) |
| `marca` | TEXT | Fabricante |
| `precio` | REAL | Precio en COP |
| `stock` | INTEGER | Unidades disponibles |
| `stock_min` | INTEGER | Umbral para la alerta de stock bajo |
| `descripcion` | TEXT | Detalle del producto |
| `creado_en` | TEXT | Fecha/hora de registro |

La regla de inventario: si `stock <= stock_min`, el producto se muestra con la etiqueta **"Bajo"** en rojo.

---

## 6. Requerimientos funcionales (RF)

| ID | Requerimiento | Criterio de aceptación |
|----|---------------|------------------------|
| RF-01 | Registrar un producto | El usuario completa el formulario y el producto queda guardado y visible en el listado. |
| RF-02 | Listar productos | La página principal muestra todos los productos en una tabla (nombre, categoría, marca, precio, stock, acciones). |
| RF-03 | Editar un producto | Se pueden modificar todos los campos de un producto existente. |
| RF-04 | Eliminar un producto | Con confirmación previa y mediante POST con CSRF, el producto se elimina. |
| RF-05 | Validar datos de entrada | Nombre obligatorio; precio y stock no negativos; se muestra mensaje de error si falla. |
| RF-06 | Alertar stock bajo | Si `stock <= stock_min`, el producto se muestra con badge rojo "Bajo". |
| RF-07 | Clasificar por categoría | El producto se asocia a una categoría seleccionable (9 categorías). |
| RF-08 | Mostrar mensajes de retroalimentación | Flash messages animados desde sesión tras cada operación. |
| RF-09 | Dashboard con KPIs | 4 indicadores en tiempo real: total productos, stock bajo, valor inventario, categorías. |
| RF-10 | Búsqueda y filtros | Buscar por nombre/marca/descripción + filtrar por categoría. |
| RF-11 | Paginación | Listado paginado automáticamente (10 productos por página). |

---

## 7. Requerimientos no funcionales (RNF)

| ID | Requerimiento |
|----|---------------|
| RNF-01 | **Usabilidad:** interfaz con sidebar responsivo, tema verde y animaciones. Tailwind CSS via CDN. |
| RNF-02 | **Seguridad:** consultas preparadas (PDO) anti-SQL injection; escape HTML (`htmlspecialchars`) anti-XSS; CSRF tokens en formularios POST; `session_regenerate_id` en login/registro; BD fuera de `public/`. |
| RNF-03 | **Portabilidad:** SQLite embebido; el proyecto corre en cualquier host con PHP. |
| RNF-04 | **Mantenibilidad:** código organizado en capas (`src/db.php`, `src/layout.php`, controladores en `public/`). |
| RNF-05 | **Rendimiento:** respuestas < 1 segundo para catálogos de hasta miles de productos. |
| RNF-06 | **Trazabilidad:** cada producto guarda su fecha de creación; tabla `auditoria` registra cada acción (crear, editar, eliminar). |
| RNF-07 | **Disponibilidad:** despliegue como demonio (Nginx + PHP-FPM) o contenedores Docker. |
| RNF-08 | **Compatibilidad:** funciona en Chrome, Firefox y Edge (versiones modernas). |

---

## 8. Flujo de pantallas

1. **Login (`/login.php`)** — card centrada con formulario de inicio de sesión.
2. **Dashboard (`/index.php?seccion=dashboard`)** — sidebar verde con KPIs, gráfico de categorías, alertas de stock bajo y productos recientes.
3. **Productos (`/index.php?seccion=productos`)** — listado completo con búsqueda, filtro por categoría y paginación.
4. **Nuevo Producto (`/index.php?seccion=registrar`)** — formulario en grid de 2 columnas.
5. **Editar (`/editar.php?id=N`)** — formulario precargado con los datos del producto.
6. **Sidebar** — 3 secciones navegables (Dashboard, Productos, Nuevo Producto) + perfil de usuario + cerrar sesión.


## 10. Despliegue paso a paso en AWS Lightsail (Nginx + PHP)

### 10.1. Crear la instancia
1. Entra a la consola de **AWS Lightsail** → **Create instance**.
2. Plataforma: **Linux/Unix** → Blueprint: **OS Only → Ubuntu 22.04 LTS** (o 24.04).
3. Elige el plan más económico (suficiente para un MVP).
4. Asigna un nombre (ej. `tienda-hardware`) y crea la instancia.
5. En la pestaña **Networking** de la instancia, crea una **IP estática** y asóciala.
6. En **Networking → Firewall**, asegúrate de tener abierto el puerto **HTTP (80)**.

### 10.2. Conectarse por SSH
Desde la consola usa el botón **Connect using SSH**, o desde tu terminal con la llave `.pem` descargada:
```bash
ssh -i LightsailDefaultKey.pem ubuntu@TU_IP_ESTATICA
```

### 10.3. Instalar Nginx, PHP-FPM y SQLite
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php-fpm php-sqlite3 git unzip
php -v          # verifica la versión instalada (ej. 8.1 / 8.3)
```
> Anota la versión de PHP (ej. `8.3`). La necesitarás para la ruta del socket de PHP-FPM.

### 10.4. Subir el código
Opción A — clonar desde GitHub:
```bash
cd /var/www
sudo git clone https://github.com/TU_USUARIO/TU_REPO.git tienda-hardware
```
Opción B — subir por SCP desde tu PC:
```bash
scp -i LightsailDefaultKey.pem -r tienda-hardware ubuntu@TU_IP_ESTATICA:/tmp/
# luego en el servidor:
sudo mv /tmp/tienda-hardware /var/www/tienda-hardware
```

### 10.5. Permisos
El usuario de Nginx/PHP (`www-data`) debe poder escribir la base de datos:
```bash
sudo chown -R www-data:www-data /var/www/tienda-hardware
sudo chmod -R 775 /var/www/tienda-hardware/database
```

### 10.6. Configurar el sitio en Nginx
Crea el archivo de configuración:
```bash
sudo nano /etc/nginx/sites-available/tienda-hardware
```
Contenido (ajusta `php8.3-fpm.sock` a tu versión real de PHP):
```nginx
server {
    listen 80;
    server_name TU_IP_ESTATICA;

    root /var/www/tienda-hardware/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

    # Bloquear acceso a archivos sensibles
    location ~ /\.(?!well-known) {
        deny all;
    }
}
```
Activa el sitio y desactiva el default:
```bash
sudo ln -s /etc/nginx/sites-available/tienda-hardware /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t            # prueba la sintaxis
sudo systemctl reload nginx
sudo systemctl enable nginx php8.1-fpm
```

### 10.7. Verificar
Abre en el navegador:
```
http://TU_IP_ESTATICA/
```
Debe cargar la página con la barra verde, el listado de productos de ejemplo y el formulario de registro.

### 10.8. (Opcional) HTTPS con dominio propio
Si asocias un dominio, instala un certificado gratuito con Certbot:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com
```

### 10.9. Solución de problemas frecuentes
| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| Error 502 Bad Gateway | Ruta del socket PHP-FPM incorrecta | Verifica `ls /run/php/` y ajusta `fastcgi_pass`. |
| Página descarga el `.php` en vez de ejecutarlo | Bloque `location ~ \.php$` mal configurado | Revisa la config de Nginx y recarga. |
| "unable to open database file" | Permisos de la carpeta `database/` | Reaplica `chown www-data` y `chmod 775`. |
| 403 Forbidden | `root` apunta a carpeta equivocada | Debe apuntar a `.../public`. |

---

## 11. Despliegue con Docker en AWS Lightsail

Esta sección describe cómo ejecutar la aplicación usando **Docker** y **Docker Compose** en una instancia Lightsail. Es una alternativa al despliegue manual con Nginx + PHP-FPM (sección 10) y ofrece las siguientes ventajas:

- Entorno reproducible y aislado.
- Sin necesidad de instalar/configurar Nginx y PHP manualmente.
- Contenedor liviano basado en `php:8.1-fpm-alpine` (~100 MB).
- Fácil de actualizar con `docker-compose pull && docker-compose up -d`.

### 11.1. Estructura de archivos Docker

```
tienda-hardware/
├── Dockerfile              # Imagen PHP 8.1 con SQLite
├── docker-compose.yml      # Orquestación de contenedores
├── .dockerignore           # Archivos excluidos de la imagen
└── docker/
    └── nginx.conf           # Configuración de Nginx para el contenedor
```

### 11.2. Requisitos en Lightsail

1. Crea una instancia Lightsail siguiendo los pasos **[10.1](#101-crear-la-instancia)** y **[10.2](#102-conectarse-por-ssh)**.
2. Una vez conectado por SSH, instala Docker:

```bash
# Actualizar paquetes
sudo apt update && sudo apt upgrade -y

# Instalar Docker y Docker Compose
sudo apt install -y docker.io docker-compose git

# Agregar tu usuario al grupo docker (para no usar sudo)
sudo usermod -aG docker $ubuntu
# Cierra sesión y vuelve a entrar para que el cambio surta efecto:
exit
# Vuelve a conectarte por SSH
```

Verifica la instalación:

```bash
docker --version
docker-compose --version
```

### 11.3. Subir el código

Opción A — clonar desde GitHub:

```bash
cd /home/ubuntu
git clone https://github.com/TU_USUARIO/TU_REPO.git tienda-hardware
cd tienda-hardware
```

Opción B — subir por SCP desde tu PC:

```bash
# En tu PC local:
scp -i LightsailDefaultKey.pem -r tienda-hardware ubuntu@TU_IP_ESTATICA:/home/ubuntu/

# En el servidor:
cd /home/ubuntu/tienda-hardware
```

### 11.4. Construir y ejecutar

```bash
# Construir la imagen PHP y levantar los contenedores
docker-compose up -d --build

# Verificar que estén corriendo
docker ps
```

Salida esperada:

```
CONTAINER ID   IMAGE                      STATUS         PORTS                NAMES
abc123def456   tienda-hardware-php        Up 2 minutes   9000/tcp             tienda-hardware-php
def456abc123   nginx:1.25-alpine          Up 2 minutes   0.0.0.0:80->80/tcp   tienda-hardware-nginx
```

### 11.5. Verificar

Abre en el navegador:

```
http://TU_IP_ESTATICA/
```

Debe cargar la página de login. Usa las credenciales por defecto:

| Correo | Contraseña |
|--------|-----------|
| `admin@tienda.com` | `admin123` |

### 11.6. Administración de contenedores

| Acción | Comando |
|--------|---------|
| Iniciar servicios | `docker-compose up -d` |
| Detener servicios | `docker-compose down` |
| Ver logs | `docker-compose logs -f` |
| Reconstruir imagen | `docker-compose up -d --build` |
| Actualizar desde GitHub | `git pull && docker-compose up -d --build` |
| Detener y eliminar volúmenes | `docker-compose down -v` |

### 11.7. Persistencia de la base de datos

La base de datos SQLite se almacena en un **bind mount** en el host:

```
./database/  →  /var/www/html/database/  (dentro del contenedor PHP)
```

Esto significa que la BD persiste aunque los contenedores se eliminen. Si quieres empezar de cero:

```bash
# Detener contenedores y eliminar la BD
docker-compose down
rm -f database/tienda.db
docker-compose up -d --build
```

### 11.8. (Opcional) HTTPS con dominio propio

Si asocias un dominio, usa Certbot **en el host** (no dentro del contenedor):

```bash
sudo apt install -y certbot
sudo certbot certonly --standalone -d tudominio.com

# Luego copia los certificados y configura Nginx
# o mejor aún: usa Caddy como proxy inverso con HTTPS automático
```

### 11.9. Solución de problemas frecuentes (Docker)

| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| Error `port 80 is already in use` | Otro servicio (como Nginx) está usando el puerto 80 | `sudo systemctl stop nginx` o cambia el puerto en `docker-compose.yml` |
| `Permission denied` al crear la BD | La carpeta `database/` no tiene permisos de escritura | `sudo chmod -R 777 database/` |
| Error 502 Bad Gateway | Nginx no puede comunicarse con PHP-FPM | Verifica que el contenedor PHP esté corriendo con `docker ps` |
| La imagen no se reconstruye | Docker usa caché | Usa `docker-compose up -d --build --no-cache` |
