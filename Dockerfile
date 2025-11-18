# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones PHP necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache (necesario para .htaccess)
RUN a2enmod rewrite

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar todos los archivos del proyecto
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Crear directorio de uploads si no existe y dar permisos
RUN mkdir -p /var/www/html/uploads/vehiculos \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/uploads

# Exponer el puerto 80
EXPOSE 80

# Apache se ejecuta automáticamente con CMD de la imagen base

