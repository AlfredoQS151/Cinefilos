# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache


# Instalar extensiones de PHP necesarias para PostgreSQL y GD (para generación de imágenes)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Copiar archivos de la aplicación al contenedor
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurar Apache para permitir .htaccess
RUN echo '<Directory "/var/www/html">\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf \
    && a2enconf override

# Exponer puerto 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]
