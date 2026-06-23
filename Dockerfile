FROM php:8.1-fpm-alpine

# Instalar extensión SQLite
RUN docker-php-ext-install pdo_sqlite

# Copiar la aplicación
WORKDIR /var/www/html
COPY . .

# Crear directorio de la base de datos con permisos
RUN mkdir -p database && \
    chown -R www-data:www-data database && \
    chmod -R 775 database

# Puerto de PHP-FPM (interno, comunicación con Nginx)
EXPOSE 9000

CMD ["php-fpm"]
